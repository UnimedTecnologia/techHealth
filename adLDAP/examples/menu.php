<?php
include (dirname(__FILE__) . "/../src/adLDAP.php");
        try {
		    $adldap = new adLDAP();
        }
        catch (adLDAPException $e) {
            echo $e; 
            exit();   
        }
		
		
		
if ($adldap->authenticate('leonardo.ridolfi', 'senhahse2019')){
			
			
            $aaa = $adldap->user()->info('leonardo.ridolfi');
			
			echo ("<pre>\n");
			//print_r($aaa);

	
	echo $aaa[0]["displayname"][0];
	
	
	
		}
		
		
		


?>
