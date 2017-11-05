<?php 

/**
* DbHandler all data base related actions( insert, update, select, delete )
*/
class DbHandler
{
	private $conn;
	function __construct()
	{
		require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db         = new DbConnect();
        $this->conn = $db->connect();
	}


	/**
     * Checking user login
     * @param String $username User login username id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkLogin($emailId, $password) {
        
        $stmt = $this->conn->prepare("SELECT * from tbl_change_agents WHERE emailId = ? AND password = ?");
        $stmt->bind_param("ss", $emailId, $password);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        //for now return true
        if( isset($user['token_expiry']) ){
            // Set new Token Here 
            $token        = $this->generateApiKey();
            $current_time = date("Y-m-d H:i:s");
            
            //getting last updated token time
            $token_expiry = strtotime($user['token_expiry']);
            //plus 7 days to last updated token time
            $expiry_time  = date( "Y-m-d H:i:s", strtotime( "$token_expiry +7 day" ) );

            if( $current_time > $token_expiry ){
                $stmt  = $this->conn->prepare("UPDATE tbl_change_agents set token = ?, updated_dt = ?, token_expiry = ? WHERE emailId = ?");
                $stmt->bind_param("ssss", $token, $current_time, $expiry_time, $emailId);
                $stmt->execute();
                $num_affected_rows = $stmt->affected_rows;
                $stmt->close();
            }

	        return true;
        }
        else
        {
            return false;
        }
    }

    public function getUserByEmail($emailId){
    	
        $stmt = $this->conn->prepare("SELECT * FROM tbl_change_agents WHERE emailId = ?");
        $stmt->bind_param("s", $emailId);
        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();

            unset($user['emailverificationcode']);
            unset($user['reg_status']);
            unset($user['register_dt']);
            unset($user['updated_dt']);

            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    public function createFarmer($data){
        // insert query
        $stmt = $this->conn->prepare("INSERT INTO farmers(name, aadhaar, gender, status) values(?, ?, ?, 1)");
        $stmt->bind_param("sss", $data['name'], $data['aadhaar'], $data['gender']);
        $result = $stmt->execute();
        $stmt->close();
        // Check for successful insertion
        if ($result) {
            // Farmer successfully inserted
            return true;
        } else {
            // Failed to create Farmer
            return false;
        }
    }

    public function isValidToken($token){
        $stmt = $this->conn->prepare("SELECT id from tbl_change_agents WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getUserId($token)
    {
        $stmt = $this->conn->prepare("SELECT id FROM tbl_change_agents WHERE token = ?");
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user_id;
        } else {
            return NULL;
        }    
    }

    /**
    * Generating random Unique MD5 String for user Api key
    */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }


    public function getFarmers($_user_id, $limit, $offset, $farmer_id = null)
    {
        $var_query  = "";

        $var_query  .= " SELECT 
                tf.fm_id, 
                tf.fm_name, 
                (SELECT file_name FROM tbl_doc_uploads WHERE doc_type = 'Profile Photo' AND fm_id=tf.fm_id) AS profile_image ,
                CONCAT(
                    trd.f7_chouse, ' ',
                    trd.f7_cstreet,  ' ',
                    trd.f7_carea,  ' ',
                    trd.f7_cstate,  ' ',
                    trd.f7_cdistrict,  ' ',
                    trd.f7_ctaluka,  ' ',
                    trd.f7_cvillage,  ' ',
                    trd.f7_cpin,  ' '
                ) as address,

                (   tp.pt_frm1 + 
                    tp.pt_frm2 + 
                    tp.pt_frm3 + 
                    tp.pt_frm4 + 
                    tp.pt_frm5 + 
                    tp.pt_frm6 + 
                    tp.pt_frm7 + 
                    tp.pt_frm8 + 
                    tp.pt_frm8_fh + 
                    tp.pt_frm9 + 
                    tp.pt_frm10 + 
                    tp.pt_frm11 + 
                    tp.pt_frm12 + 
                    tp.pt_frm13 + 
                    tp.pt_frm14  ) AS points

            FROM tbl_farmers AS tf LEFT JOIN tbl_residence_details AS trd
                ON tf.fm_id = trd.fm_id LEFT JOIN tbl_points AS tp
                ON tf.fm_id = tp.fm_id 
                WHERE tf.fm_caid= ?  ";


        if($farmer_id != null)
        {
            $var_query .= "  AND tf.fm_id = ? ";
        }
        
        $var_query .= " LIMIT ".$limit.", ".$offset." ";

        // $var_query .= " LIMIT 0, 5 ";

        $stmt = $this->conn->prepare($var_query);

        if($farmer_id != null)
        {
            $stmt->bind_param("ii", $_user_id, $farmer_id);
        }else{
            $stmt->bind_param("i", $_user_id);
        }



        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $users = [];

            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }

            $stmt->close();
            return $users;
        } else {
            return  NULL;
        }
    }

    // ====================================================================
    // START : LMS
    // ====================================================================
    public function getLMS()
    {
        $sql_get_farmer_details = " SELECT tf.fm_id AS fm_id, tf.fm_name AS farmer_name, ";
        $sql_get_farmer_details .= " tf.fm_mobileno AS farmer_mobile_number, ";
        $sql_get_farmer_details .= " tf.fm_aadhar AS farmer_aadhar_number, tpd.f1_mfname AS ";
        $sql_get_farmer_details .= " mother_name, tpd.f1_father AS father_name, tpd.f1_dob AS DOB, ";
        $sql_get_farmer_details .= " CONCAT(trd.f7_phouse, ' ', trd.f7_pstreet, ' ', trd.f7_parea) ";
        $sql_get_farmer_details .= " AS address, ts.st_name AS farmer_state, td.dt_name AS ";
        $sql_get_farmer_details .= " farmer_district, tt.tk_name AS farmer_taluka, tv.vl_name AS ";
        $sql_get_farmer_details .= " farmer_village, trd.f7_ppin AS farmer_pincode, ";
        $sql_get_farmer_details .= " tsd.f3_spouse_fname AS spouse_name, tsd.f3_spouse_mobno AS ";
        $sql_get_farmer_details .= " spouse_mobile_number, tsd.f3_spouse_adhno AS ";
        $sql_get_farmer_details .= " spouse_aadhar_number, tld.f9_land_size AS land_size, ";
        $sql_get_farmer_details .= " tld.f9_survey_number AS survey_number, tld.f9_gat_number AS ";
        $sql_get_farmer_details .= " gat_number, ";
        $sql_get_farmer_details .= " (SELECT st_name FROM tbl_state WHERE id=tld.f9_state) AS ";
        $sql_get_farmer_details .= " land_state, (SELECT dt_name FROM tbl_district WHERE ";
        $sql_get_farmer_details .= "  id=tld.f9_district) AS land_district, (SELECT tk_name ";
        $sql_get_farmer_details .= " FROM tbl_taluka WHERE id=tld.f9_taluka) AS land_taluka, ";
        $sql_get_farmer_details .= " (SELECT vl_name FROM tbl_village WHERE id=tld.f9_vilage) ";
        $sql_get_farmer_details .= " AS land_village, tld.f9_pincode AS land_pincode ";
        $sql_get_farmer_details .= " FROM tbl_farmers AS tf INNER JOIN tbl_personal_detail AS tpd  ";
        $sql_get_farmer_details .= " ON tf.fm_id = tpd.fm_id INNER JOIN tbl_residence_details AS trd ";
        $sql_get_farmer_details .= " ON tf.fm_id = trd.fm_id INNER JOIN tbl_land_details AS tld ";
        $sql_get_farmer_details .= " ON tf.fm_id = tld.fm_id INNER JOIN tbl_state AS ts ";
        $sql_get_farmer_details .= " ON trd.f7_pstate = ts.id INNER JOIN tbl_district AS td ";
        $sql_get_farmer_details .= " ON trd.f7_pdistrict = td.id INNER JOIN tbl_taluka AS tt ";
        $sql_get_farmer_details .= " ON trd.f7_ptaluka = tt.id INNER JOIN tbl_village AS tv ";
        $sql_get_farmer_details .= " ON trd.f7_pvillage = tv.id INNER JOIN tbl_spouse_details AS tsd ";
        $sql_get_farmer_details .= " ON tf.fm_id = tsd.fm_id ";

        $stmt = $this->conn->prepare($sql_get_farmer_details);
        //$stmt->bind_param("ii", $_user_id, $farmer_id);
        if ($stmt->execute()) 
        {
            $result = $stmt->get_result();
            $lms = [];
            while ($row = $result->fetch_assoc()) 
            {
                // For Farmer Profile Image
                $farmer_id              = $row['fm_id'];
                $farmer_profile_img     = '';
                $row['profile_image']   = '';
                $farmer_aadhar_card_img = '';
                $row['aadhaar_image']   = '';

                // Query For Profile Image
                $stmt1 = $this->conn->prepare("SELECT * FROM `tbl_doc_uploads` WHERE `fm_id` = ? AND `doc_type`= 'Profile Photo'");
                $stmt1->bind_param("i", $farmer_id);
                if ($stmt1->execute()) 
                {
                    $result1 = $stmt1->get_result();
                    $farmer_profile_img_name    = '';
                    while ($row1 = $result1->fetch_assoc())
                    {
                        $farmer_profile_img_name    = $row1['file_name']; 
                    }

                    if($farmer_profile_img_name != '')
                    {
                        $farmer_profile_img   = 'http://www.sqoreyard.com/sqyardpanel/data/'.$farmer_id.'/'.$farmer_profile_img_name;
                        $row['profile_image'] = $farmer_profile_img;
                    }
                }
                

                // Query For Aadhar Card Image
                $stmt2 = $this->conn->prepare("SELECT * FROM `tbl_doc_uploads` WHERE `fm_id` = ? AND `doc_type`= 'Aadhar'");
                $stmt2->bind_param("i", $farmer_id);
                if ($stmt2->execute()) 
                {
                    $result2 = $stmt2->get_result();
                    $farmer_aadhar_card_img_name    = '';
                    while ($row2 = $result2->fetch_assoc())
                    {
                        $farmer_aadhar_card_img_name    = $row2['file_name']; 
                    }

                    if($farmer_aadhar_card_img_name != '')
                    {
                        $farmer_aadhar_card_img = 'http://www.sqoreyard.com/sqyardpanel/data/'.$farmer_id.'/'.$farmer_aadhar_card_img_name;
                        $row['aadhaar_image']   = $farmer_aadhar_card_img;
                    }

                }
                
                
                $lms[] = $row;

            }
            $stmt->close();
            return $lms;
        } 
        else 
        {
            return  NULL;
        }
    }
    // ====================================================================
    // END : LMS
    // ====================================================================

    // ====================================================================
    // START : Kyc Knowladge
    // ====================================================================
    public function crateKycKnowladgeEntry($data, $_user_id)
    {
        // insert query
        $dat = date("Y-m-d H:i:s"); // Get Current DateTime for Created Date

        $stmt = $this->conn->prepare("INSERT INTO `tbl_applicant_knowledge`(`fm_caid`, `fm_id`, `f2_edudetail`, `f2_proficiency`, `f2_participation`, `f2_typeprog`, `f2_nameprog`, `f2_dateprog`, `f2_durprog`, `f2_condprog`, `f2_cropprog`, `f2_status`, `f2_section_id`, `f2_points`, `f2_created_date`, `f2_created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssssssssissss", $_user_id, $data['fm_id'], $data['f2_edudetail'], $data['f2_proficiency'], $data['f2_participation'], $data['f2_typeprog'], $data['f2_nameprog'], $data['f2_dateprog'], $data['f2_durprog'], $data['f2_condprog'], $data['f2_cropprog'], '1', '', $data['f2_points'], $dat, $_user_id);
        $result = $stmt->execute();
        $stmt->close();
        // Check for successful insertion
        if ($result) {
            // Farmer successfully inserted
            return true;
        } else {
            // Failed to create Farmer
            return false;
        }
    }
    
    public function updateKycKnowladgeEntry($data, $_user_id, $farmer_id)
    {
        // Update query
        $dat = date("Y-m-d H:i:s"); // Get Current DateTime for Modified Date

        $stmt = $this->conn->prepare(" UPDATE `tbl_applicant_knowledge` SET `f2_edudetail` = ?, `f2_proficiency` = ?, `f2_participation` = ?, `f2_typeprog` = ?, `f2_nameprog` = ?, `f2_dateprog` = ?, `f2_durprog` = ?, `f2_condprog` = ?, `f2_cropprog` = ?, `f2_status` = '1', `f2_section_id` = ?, `f2_points` = ?, `f2_modified_date` = ?, `f2_modified_by` = ? WHERE `fm_id`= ? ");
        $stmt->bind_param("sssssssssissssi", $data['f2_edudetail'], $data['f2_proficiency'], $data['f2_participation'], $data['f2_typeprog'], $data['f2_nameprog'], $data['f2_dateprog'], $data['f2_durprog'], $data['f2_condprog'], $data['f2_cropprog'], '1', '', $data['f2_points'], $dat, $_user_id, $farmer_id);
        $result = $stmt->execute();
        $stmt->close();
        // Check for successful insertion
        if ($result) {
            // Farmer successfully inserted
            return true;
        } else {
            // Failed to create Farmer
            return false;
        } 
    }

    public function getKycKnowladge($_user_id, $farmer_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM tbl_applicant_knowledge WHERE fm_caid = ? AND fm_id = ?");
        $stmt->bind_param("ii", $_user_id, $farmer_id);
        if ($stmt->execute()) 
        {
            $result = $stmt->get_result();
            $KycKnowladge = [];
            while ($row = $result->fetch_assoc()) 
            {
                $KycKnowladge[] = $row;
            }
            $stmt->close();
            return $KycKnowladge;
        } 
        else 
        {
            return  NULL;
        }
    }

    // ====================================================================
    // END : Kyc Knowladge
    // ====================================================================


    // ====================================================================
    // START : Kyc Knowladge
    // ====================================================================
    public function crateKycSpouseEntry($data, $_user_id)
    {
        // insert query
        $dat = date("Y-m-d H:i:s"); // Get Current DateTime for Created Date

        $stmt = $this->conn->prepare(" INSERT INTO `tbl_spouse_details`(`fm_caid`, `fm_id`, `f3_married`, `f3_spouse_fname`, `f3_spouse_dob`, `f3_spouse_age`, `f3_spouse_mobno`, `f3_spouse_adhno`, `f3_english_profeciency`, `f3_spouse_occp`, `f3_spouse_owned_prop`, `f3_spouse_prop_type`, `f3_property_details`, `f3_spouse_get_any_income`, `f3_spouse_yearly_income`, `f3_spouse_income`, `f3_status`, `f3_section_id`, `f3_points`, `f3_married_reg_points`, `f3_created_date`, `f3_created_by`, `f3_is_fpo_member`, `f3_bank_name`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ");
        $stmt->bind_param("iissssssssssssssisssssss", $_user_id, $data['fm_id'], $data['f3_married'], $data['f3_spouse_fname'], $data['f3_spouse_fname'], $data['f3_spouse_dob'], $data['f3_spouse_age'], $data['f3_spouse_mobno'], $data['f3_spouse_adhno'], $data['f3_english_profeciency'], $data['f3_spouse_occp'], $data['f3_spouse_owned_prop'], $data['f3_spouse_prop_type'], $data['f3_property_details'], $data['f3_spouse_get_any_income'], $data['f3_spouse_yearly_income'], $data['f3_spouse_income'], '1', '', $data['f3_points'], $data['f3_married_reg_points'], $dat, $_user_id, $data['f3_is_fpo_member'], $data['f3_bank_name']);
        $result = $stmt->execute();
        $stmt->close();
        // Check for successful insertion
        if ($result) {
            // Farmer successfully inserted
            return true;
        } else {
            // Failed to create Farmer
            return false;
        }
    }
    
    public function updateKycSpouseEntry($data, $_user_id, $farmer_id)
    {
        // Update query
        $dat = date("Y-m-d H:i:s"); // Get Current DateTime for Modified Date

        $stmt = $this->conn->prepare(" UPDATE `tbl_spouse_details` SET `f3_married`= ?,`f3_spouse_fname`= ?,`f3_spouse_dob`= ?,`f3_spouse_age`= ?,`f3_spouse_mobno`= ?,`f3_spouse_adhno`= ?,`f3_english_profeciency`= ?,`f3_spouse_occp`= ?,`f3_spouse_owned_prop`= ?,`f3_spouse_prop_type`= ?,`f3_property_details`= ?,`f3_spouse_get_any_income`= ?,`f3_spouse_yearly_income`= ?,`f3_spouse_income`= ?,`f3_status`= ?,`f3_section_id`= ?,`f3_points`= ?,`f3_married_reg_points`= ?,`f3_modified_date`= ?,`f3_modified_by`= ?,`f3_is_fpo_member`= ?,`f3_bank_name`= ? WHERE `fm_caid` = ?  AND `fm_id` = ? ");
        $stmt->bind_param("ssssssssssssssissssissii",$data['f3_married'], $data['f3_spouse_fname'], $data['f3_spouse_dob'], $data['f3_spouse_age'], $data['f3_spouse_mobno'], $data['f3_spouse_adhno'], $data['f3_english_profeciency'], $data['f3_spouse_occp'], $data['f3_spouse_owned_prop'], $data['f3_spouse_prop_type'], $data['f3_property_details'], $data['f3_spouse_get_any_income'], $data['f3_spouse_yearly_income'], $data['f3_spouse_income'], '1', '', $data['f3_points'], $data['f3_married_reg_points'], $dat, $_user_id, $data['f3_is_fpo_member'], $data['f3_bank_name'], $_user_id, $farmer_id);
        $result = $stmt->execute();
        $stmt->close();
        // Check for successful insertion
        if ($result) {
            // Farmer successfully inserted
            return true;
        } else {
            // Failed to create Farmer
            return false;
        } 
    }

    public function getKycSpouseDetails($_user_id, $farmer_id)
    {
        $stmt = $this->conn->prepare(" SELECT * FROM tbl_spouse_details WHERE fm_caid = ? AND fm_id = ? ");
        $stmt->bind_param("ii", $_user_id, $farmer_id);
        if ($stmt->execute()) 
        {
            $result = $stmt->get_result();
            $KycSpouseDetails = [];
            while ($row = $result->fetch_assoc()) 
            {
                $KycSpouseDetails[] = $row;
            }
            $stmt->close();
            return $KycSpouseDetails;
        } 
        else 
        {
            return  NULL;
        }
    }

    // ====================================================================
    // END : Kyc Knowladge
    // ====================================================================

    
}