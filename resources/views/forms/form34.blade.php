<!DOCTYPE html>
    <head>
        <meta charset="UTF-8">
        <style>
        table,
        th,
        td {
            border: #212121 solid 1px;
            border-collapse: collapse;
            margin-right: 0px;
            margin-left: 0px;
            margin-bottom: 0px;
            font-size: 14px;
            ;
            padding: 5px;
            font-family: 'sans-serif'goeUI';
        }

        table{
            page-break-inside: avoid;
            table-layout:fixed;
        }

        font {
            margin-left: 0px;
            margin-right: 0px;
            font-size: 14px;
            font-family: 'sans-serif';
            margin-bottom: 1px;
        }

        .bg-color {
            background: #b9b9b9;
        }

        .a {
            /* height: 5cm; */
            line-height: 12px;
            border-bottom: #000000;
            border-top: #000000;
            background: #dedcdc;
            position: relative;
        }

        /* .a> h6 {
            float: left;
            transform: rotate(-90deg);
            -webkit-transform: rotate(-90deg);  
            -ms-transform: rotate(-90deg); 
       

        } */

        body {
            margin-left: 2.5cm;
            font-family: 'sans-serif';

        }
    </style>
    </head>
    <body>
    <section class="form-body">
       
        <table width="100%" style="border:0; padding:0;" autosize="1">
            <tr>
                <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{ URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 34<br><br></b></span><p style="font-size: 13px; ">Notice of </p><p style="font-size:16px;"><b>APPOINTMENT OF ADMINISTRATOR</b></p></td>
                <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 415(7))</td>
                <td width="20%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>
			
            
            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">In terms of Section  415(7) of the Companies Act No. 7 of 2007</td>
            </tr>
        </table>
        <br>
        <br>
        
        <?php
        $pv_first = '';
        $pv_second = '';
        $pv_number_part = '';
        if($certificate_no){
            $pv_first =  substr($certificate_no,0,1);
            $pv_second =  substr($certificate_no,1,1);
            $pv_number_part =  substr($certificate_no,2);
        }
        ?>
        <table style="border: 0;"  width="100%" autosize="1" >
                <tbody>
                    <tr>
                        <td width="25%" height="35" style="border: 0;">Number of the Company</td>
                        <td width="7%" height="35" style="text-align:center;font-weight:bold;font-size:20px"><?php echo $pv_first; ?></td>
                        <td width="7%" height="35" style="text-align:center;font-weight:bold;font-size:20px"><?php echo $pv_second; ?></td>
                        <td width="61%" height="35" style="font-weight:bold;font-size:20px">&nbsp;<?php echo $pv_number_part; ?></td>
                    </tr>
                </tbody>
            </table>
        <br>
        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="117.3pt" height="40" class="bg-color">Name of the Company</td>
                    <td width="392.7pt" height="40"><?php echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?></td>
                </tr>
            </tbody>
        </table>
        <br>
        <br>
        
        <?php

            $savedDirectorsCount = count($savedDirectorsNames);
            $i_or_we_director = ($savedDirectorsCount >1) ? "We" : "I";


        ?>
		
		<!-- <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td style="border: 0"><div align="justify"> <?php // echo $i_or_we_director;?> <?php // echo implode(',', $savedDirectorsNames);?>  of 
                    <?php // echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?> hereby give notice that :-

</div> </td>
                </tr>
            </tbody>
        </table>
        <br> -->
        

        <?php

        $admin_count = count($records);
        $admin_list = array();
        if($admin_count){
            foreach($records as $admin ) {
                $admin_list[] = $admin['firstname'].' '.$admin['lastname'];
                
            }
        }

        $i_or_we = ($admin_count >1) ? "We  ".implode(',', $admin_list)." were appointed Administrators" : "I ".implode(',', $admin_list)." was appointed Administrator";

       

      

        $appointed_by_text = '';

        if($appointed_by == 'court_order') {
            $court_date = strtotime($court_date);
            $court_d = date('d', $court_date);
            $court_y = date('Y', $court_date);
            $court_m = date("F",  $court_date);
            $appointed_by_text = 'by the order of Court dated '.$court_d.' day of '. $court_m.' '.$court_y;
        }
        
        if($appointed_by == 'resolution') {
            $appointed_by_text = 'by the Board of Directors of the Company';
        }
        
        


          ?>
		
		
		<!-- <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
					<td valign="top" style= "border:0" width="10.2pt">
						(a) 
					</td>	
                    <td style="border: 0" width="499.8pt" align="justify"><?php // echo $i_or_we; ?> of <?php // echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?><?php // echo implode(',', $admin_list);?>  <?php // echo $appointed_by_text;?>

                </tr>
                <br>
                <br>
				
            </tbody>
        </table> -->
		
		
		<br>
        <br>
        <?php
             $date_of = $date_of ? strtotime($date_of) : null;
             if($date_of) {
                 $d = date('d', $date_of);
                 $m = date('m', $date_of);
                 $y = date('Y', $date_of);
             } else {
                 $d = $m = $y = null;
             }
             ?>
        <table style="border: 0" width="100%" >
            <tbody>
            <tr>
            <td height="30" align="right" style="border:0">Date:</td>
                    <td width="8%" style="border:0"    ></td>
                    <td width="5%" style="text-align:center;"><?php echo isset($d[0]) ? $d[0] : '' ; ?></td>
                    <td width="5%" style="text-align:center;"><?php echo isset($d[1]) ? $d[1] : '' ; ?></td>
                    <td width="10%"  style="border:0"   ></td>
                    <td width="5%" style="text-align:center;"><?php echo isset($m[0]) ? $m[0] : '' ; ?></td>
                    <td width="5%" style="text-align:center;"><?php echo isset($m[1]) ? $m[1] : '' ; ?></td>
                    <td width="10%" style="border:0"    ></td>
                    <td width="5%" style="text-align:center;"><?php echo isset($y[0]) ? $y[0] : '' ; ?></td>
                    <td width="5%" style="text-align:center;"><?php echo isset($y[1]) ? $y[1] : '' ; ?></td>
                    <td width="5%" style="text-align:center;"><?php echo isset($y[2]) ? $y[2] : '' ; ?></td>
                    <td width="5%" style="text-align:center;"><?php echo isset($y[3]) ? $y[3] : '' ; ?></td>
                    <td width="4%" style="border:0"    ></td>
            </tr>
            <tr>
            <td width="24%" height="5" style="border: 0" align="right"></td>
                <td width="3%" style="border: 0"> </td>
                <td colspan="2" class="bg-color">
                <center>Day</center>
                </td>
                <td width="8%" style="border: 0"></td>
                <td colspan="2" class="bg-color">
                <center>Month</center>
                </td>
                <td width="8%" style="border: 0"></td>
                <td colspan="4" class="bg-color">
                <center>Year</center>
                </td>
            </tr>
            </tbody>
        </table>
		<br>
		<br>
	
	<!--<table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td align="left" width="117.3pt" height="75" class="bg-color" >Signature of Administartor</td>
                    <td width="392.3pt" height="75">
                </tr>
            </tbody>
    </table> -->

     <?php 

//print_r($records);
?>

    <table width="100%" autosize="1">
           <tr>
                <td colspan="5" style="width: 509pt; border-bottom; border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 13px;"> <strong><font ><b>List of Administrators:</b></font></strong></td>
            </tr>
            <tr>
                <td width="20%" align="center" class="bg-color">Full Name</td>
                <td width="25%" align="center" class="bg-color">NIC/Passport</td>
                <td width="25%" align="center" class="bg-color">Office Address</td>
                <td width="10%" align="center" class="bg-color">Appointed Date</td>
                <td width="20%" align="center" class="bg-color">Signature</td>
            </tr>
            <?php 
             
           
                if(count($records)) {
                    foreach($records as $d) { ?>

                         <tr>
                            <td width="20%" height="50"><?php echo ( $d['firstname']. ' '. $d['lastname'] );?></td>
                            <td width="25%" height="50">
                                <?php
                                  if($d['type'] == 'local') {
                                      echo '<strong>NIC:</strong> '. $d['nic'];
                                  }
                                  if($d['type'] == 'foreign') {
                                    echo '<strong>Passport:</strong> '. $d['passport'];
                                    echo '<br/><strong>Issued Country:</strong> '. $d['passport_issued_country'];
                                }
                           
                             
                                ?>
                            </td>
                            <td width="25%" height="50">


                                <?php
                                     $line1 = $d['officeAddress1'].',';
                                     $line2 = ($d['officeAddress2']) ? $d['officeAddress2']. '' : '';
                                     $city = ' ';
                                     $post_code = $d['officePostcode'];
         
                                     echo $line1.$line2.$city.$post_code;

                                ?>
                            </td>
                            <td width="10%" height="50" align="center">

                               <?php

                                    if($d['appointed_by'] == 'court_order') {
                                        echo $d['court_date'] .'<br/><small>(by the order of Court)</small>';
                                    }

                                    if($d['appointed_by'] == 'resolution') {
                                        echo $d['resolution_date'] .'<br/><small>(by Resolution)</small>';
                                    }

                               ?>
                            </td>
                            <td width="20%" height="50"></td>
                        </tr>
                     <?php 

                    }

                }
            
             ?>

        </table>
		<br>
		<br>
		<br>
	
		<table style="border-color: #FFFFFF;" width="100%" height="30" autosize="1">
            <tbody>
                <tr>
                    <td align="left" style="border:0;">
Note : This Notice should be delivered to the Registrar-General of Companies, within
10 working days of his appointment.


					</td>
                </tr>
            </tbody>
        </table>
		<br>
		
		<!-- <table style="border-color: #FFFFFF;" width="100%" height="30" align="center" autosize="1">
            <tbody>
                <tr>
                    <td align="left" style="border:0;"><span style="font-size: 13px;">*  Delete what is not applicable
					</td>
                </tr>
            </tbody>
        </table> -->
		
		<br>
		
    </body>
</html>