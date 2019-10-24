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
                padding: 5px;
                font-family: 'SegoeUI',sans-serif;
            }
            table{
            page-break-inside: avoid;
            table-layout:fixed;
        }

            font {
                margin-left: 0px;
                margin-right: 0px;
                font-size: 14px;
                font-family: 'SegoeUI',sans-serif;
                margin-bottom: 1px;
            }

            .bg-color {
                background: #D3D3D3;
            }

            .a {
                /* height: 5cm; */
                line-height: 12px;
                border-bottom: #000000;
                border-top: #000000;
                background: #dedcdc;
                position: relative;
            }

            body {
                margin-left: 2.5cm;
                margin-right: 2.5cm;
                font-family: 'SegoeUI',sans-serif;

            }
        </style>

    </head>

    <body>

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
    

    <section class="form-body">

        <table width="100%" style="border:0; padding:0;" autosize="1">
            <tr>
                <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 35<br><br></b></span><p style="font-size: 13px; ">Return of</p><p style="font-size:16px;"><b>ALTERATIONS OF PARTICULARS OF OVERSEAS COMPANY</b></p></td>
                <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 491)</td>
                <td width="20%" style="border:0; padding:0px;"><img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>    
            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">Section 491 of the Companies Act No. 7 of 2007</td>
            </tr>
        </table>

        <br>

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
                    <td width="23%" height="40" class="bg-color";>Name of the Company </td>
                    <td width="77%" height="40"><?php echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?></td>
                </tr>
            </tbody>
        </table>
        
        <br>

		<table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td style="border: 0; word-break: break-all; text-align:justify">(1)	Return of amendment of charter, statute, or memorandum and articles of association of the company or other 
			            instrument constituting or defining the constitution of the company.
                    </td>
                </tr>
            </tbody>
        </table>

        <br><br>

        <?php
            $date_of = $charter_change_date ? strtotime($charter_change_date) : null;
            if($date_of) {
                $d = date('d', $date_of);
                $m = date('m', $date_of);
                $y = date('Y', $date_of);
            } else {
                $d = $m = $y = null;
            }
            

        ?>
		 
		<table  width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
					<td width="15%" height="30" style="border: 0;">The Date of Change of Charter</td>
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
                    <td width="30%" height="22"  style="border:0"   ></td>
                    <td width="10%" style="border:0"    > </td>
                    <td colspan="2" class="bg-color">
                        <center>Day</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="2" class="bg-color" >
                        <center>Month</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="4" class="bg-color" >
                        <center>Year</center>
                    </td>
                </tr>
			</tbody>
        </table>
        
        <br/>

        <?php
            $date_of = $statute_change_date ? strtotime($statute_change_date) : null;
            if($date_of) {
                $d = date('d', $date_of);
                $m = date('m', $date_of);
                $y = date('Y', $date_of);
            } else {
                $d = $m = $y = null;
            }
            

        ?>
		 
		<table  width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
					<td width="15%" height="30" style="border: 0;">The Date of Change of Statute</td>
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
                    <td width="30%" height="22"  style="border:0"   ></td>
                    <td width="10%" style="border:0"    > </td>
                    <td colspan="2" class="bg-color">
                        <center>Day</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="2" class="bg-color" >
                        <center>Month</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="4" class="bg-color" >
                        <center>Year</center>
                    </td>
                </tr>
			</tbody>
        </table>
        <br/>

         <?php
            $date_of = $memorandum_change_date ? strtotime($memorandum_change_date) : null;
            if($date_of) {
                $d = date('d', $date_of);
                $m = date('m', $date_of);
                $y = date('Y', $date_of);
            } else {
                $d = $m = $y = null;
            }
            

        ?>
		 
		<table  width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
					<td width="15%" height="30" style="border: 0;">The Date of Change of Memorandum</td>
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
                    <td width="30%" height="22"  style="border:0"   ></td>
                    <td width="10%" style="border:0"    > </td>
                    <td colspan="2" class="bg-color">
                        <center>Day</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="2" class="bg-color" >
                        <center>Month</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="4" class="bg-color" >
                        <center>Year</center>
                    </td>
                </tr>
			</tbody>
        </table>
        <br/>

         <?php
            $date_of = $article_change_date ? strtotime($article_change_date) : null;
            if($date_of) {
                $d = date('d', $date_of);
                $m = date('m', $date_of);
                $y = date('Y', $date_of);
            } else {
                $d = $m = $y = null;
            }
            

        ?>
        <br/>
		 
		<table  width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
					<td width="15%" height="30" style="border: 0;">The Date of Change of Article</td>
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
                    <td width="30%" height="22"  style="border:0"   ></td>
                    <td width="10%" style="border:0"    > </td>
                    <td colspan="2" class="bg-color">
                        <center>Day</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="2" class="bg-color" >
                        <center>Month</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="4" class="bg-color" >
                        <center>Year</center>
                    </td>
                </tr>
			</tbody>
		</table>

       <br><br> 

       <?php

        $director_changes = $stakeholder_changes[0];

        ?>

		<table style="border: 0;" width="100%" autosize="1">
            <tbody>

                <tr> 
                    <td style="border: 0; word-break: break-all; text-align:justify" colspan="4">Annex a copy of the altered charter, statute, or memorandum and articles of association of the company or other 
                    instrument constituting or defining the constitution of the company and where such instrument is not in an official 
                    language or in English, along with a translation of such instrument in English.
                    </td>
	            </tr>
                <tr>
                    <td style="border: 0;" colspan="4">(2)	Return of alteration of Directors of the Company or their particulars</td>
                </tr>
                <tr>
                    <td width="127pt"> Full name of Director </td>
                    <td width="128pt"> Residential Address</td>
                    <td width="127pt"> Other business or occupation </td>
                    <td width="127pt"> State changes giving date of changes </td>
                </tr>

                <?php if(count($director_changes)) { 
                    foreach($director_changes as $director) {
                        ?>

                      <tr height="100">
                        <td><?php echo $director['full_name'];?></td>
                        <td>

                            <?php if(isset($director['address']['id'] ) && $director['address']['id'] ) {
                                ?>

                                 <p style="font-weight:bold;text-decoration:underline">Local Address</p>
                             
                                <?php 

                                echo $director['address']['address1'].',<br/>';
                                echo ($director['address']['address2']) ?$director['address']['address2'].',<br/>' : '';
                            //    echo ($director['address']['city']) ?$director['address']['city'].',<br/>' : '';
                                echo $director['address']['postcode'];
                            }

                            if(isset($director['for_address']['id'] ) && $director['for_address']['id'] ) {

                               ?>

                                 <p style="font-weight:bold;text-decoration:underline">Foreign Address</p>
                             
                                <?php 

                                echo $director['for_address']['address1'].',<br/>';
                                echo ($director['for_address']['address2']) ?$director['for_address']['address2'].',<br/>' : '';
                                echo ($director['for_address']['city']) ?$director['for_address']['city'].',<br/>' : '';
                                echo ($director['for_address']['province']) ?$director['for_address']['province'].',<br/>' : '';
                                echo $director['for_address']['postcode'];
                            }

                            ?>

                          
                        </td>
                        <td><?php echo $director['occupation']; ?></td>
                        <td><?php
                                  echo '<strong>State: </strong>'. $director['change'].'<br/>'; 
                                  echo ($director['change'] == 'No Change') ? '' :  '<strong>Date of Changes: </strong>'.$director['effective_date'];
                            ?>
                            </td>
                      </tr>


                        <?php 
                    }


                 } else {
                    ?>

                    <tr height="100">
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php 
                }
                ?>

				
            </tbody>
        </table>

	
		 <br/> <br/>
		<table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td style="border: 0;" >(3) Return of alteration of particulars of the person(s) authorized to accept documents and notices in Sri Lanka.</td>
                </tr>
            </tbody>
        </table>
        
        <br><br>

		<table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="178pt"> Full name </td>
                    <td width="179pt">  Residential Address</td>
                    <td width="152pt"> State changes giving date of changes </td>
                </tr>
				<tr height="100">
                 

                 <?php
                $sec_changes = $stakeholder_changes[1];
                 
                 if(count($sec_changes)) { 
                    foreach($sec_changes as $sec) {
                        ?>

                      <tr height="100">
                        <td><?php echo $sec['full_name'];?>(<?php echo $sec['stakeholder_type'];?>)</td>
                        <td>

                            <?php if(isset($sec['address']['id'] ) && $sec['address']['id'] ) {
                                ?>

                                 <p style="font-weight:bold;text-decoration:underline">Local Address</p>
                             
                                <?php 

                                echo $sec['address']['address1'].',<br/>';
                                echo ($sec['address']['address2']) ?$sec['address']['address2'].',<br/>' : '';
                              //  echo ($sec['address']['city']) ?$sec['address']['city'].',<br/>' : '';
                                echo ($sec['stakeholder_type'] == 'Power of Attorney firm' && $sec['is_srilankan'] == 'no' &&  $sec['address']['province'] ) ? $sec['address']['province'].',<br/>' : '';
                                echo $sec['address']['postcode'];
                            }

                            if(isset($sec['for_address']['id'] ) && $sec['for_address']['id'] ) {
                                ?>

                                 <p style="font-weight:bold;text-decoration:underline">Foreign Address</p>
                             
                                <?php 


                                echo $sec['for_address']['address1'].',<br/>';
                                echo ($sec['for_address']['address2']) ?$sec['for_address']['address2'].',<br/>' : '';
                                echo ($sec['for_address']['city']) ?$sec['for_address']['city'].',<br/>' : '';
                                echo ($sec['for_address']['province']) ?$sec['for_address']['province'].',<br/>' : '';
                                echo $sec['for_address']['postcode'];
                            }

                            ?>

                          
                        </td>
                        <td><?php
                                  echo '<strong>State: </strong>'.$sec['change'].'<br/>'; 
                                  echo ($sec['change'] == 'No Change') ? '' :  '<strong>Date of Changes: </strong>'.$sec['effective_date'];
                            ?>
                            </td>
                      </tr>


                        <?php 
                    }


                 } else {
                    ?>

                    <tr height="100">
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php 
                }
                ?>



            </tbody>
        </table>
        
        <br>

		<table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td style=" border:0; word-break: break-all; text-align:justify" >(4)	Return of alteration of address of the  Registered or principal office of the Company.</td>
                </tr>
            </tbody>
        </table>
        
        <br>

		<table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="153pt" height="80" class="bg-color">Previous Address</td>
                    <td  height="80">


                        <?php

                        if($company_for_address) {

                            echo $company_for_address->address1.',<br/>'; 
                            echo ($company_for_address->address2) ? $company_for_address->address2.',<br/>' : '';
                            echo $company_for_address->city.',<br/>';
                            echo $company_for_address->province.',<br/>';
                            echo $company_for_address->country;


                        }



                        ?>



                    </td>
                </tr>
            </tbody>
        </table>
        
        <br><br>

		<table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="153pt" height="80" class="bg-color">New Address</td>
                    <td  height="80">

                      <?php 
                         if($has_request_for_address) {

                           echo $request_for_address->address1.',<br/>'; 
                           echo ($request_for_address->address2) ? $request_for_address->address2.',<br/>' : '';
                           echo $request_for_address->city.',<br/>';
                           echo $request_for_address->province.',<br/>';
                          echo $request_for_address->country;


                            }
                         ?>

                    </td>
                </tr>
            </tbody>
        </table>
        
        <br>

         <?php
            $date_a = $has_request_for_address && $request_for_address_change_date ? strtotime($request_for_address_change_date) : null;
          
            if($date_a) {
                $d = date('d', $date_a);
                $m = date('m', $date_a);
                $y = date('Y', $date_a);
            } else {
                $d = $m = $y = null;
            }
            

        ?>

		<table  width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
					<td width="15%" height="30" style="border: 0;">The Date of Change of Address </td>
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
                    <td width="30%" height="22"  style="border:0"   ></td>
                    <td width="10%" style="border:0"    > </td>
                    <td colspan="2" class="bg-color">
                        <center>Day</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="2" class="bg-color" >
                        <center>Month</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="4" class="bg-color" >
                        <center>Year</center>
                    </td>
                </tr>
			</tbody>
		</table> 
        
        <br>

        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td style=" border:0; word-break: break-all; text-align:justify">(5)	Return of alteration of address of the principal place of business of the company within Sri Lanka.</td>
                </tr>
            </tbody>
        </table>
        
        <br>

		<table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="153pt" height="80" class="bg-color">Previous Address</td>
                    <td height="80">
                    <?php

                        if(isset($company_address->address1) && $company_address->address1 ) {

                            echo $company_address->address1.',<br/>';
                            echo ($company_address->address2) ? $company_address->address2.',<br/>' : '';
                            echo $company_address->city.',<br/>';
                            echo '<strong>postcode: </strong>'.$company_address->postcode;

                        }



                        ?>

                    </td>
                </tr>
            </tbody>
        </table>
        
        <br>

		<table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="153pt" height="80" class="bg-color">New Address</td>
                    <td  height="80">

                    <?php

                        if($has_request_address) {

                            echo $request_address->address1.',<br/>';
                            echo ($request_address->address2) ? $request_address->address2.',<br/>' : '';
                            echo $request_address->city.',<br/>';
                            echo '<strong>postcode: </strong>'.$request_address->postcode;

                        }


                    ?>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <br><br><br>
        <?php
            $date_b = $has_request_address && $request_address_change_date ? strtotime($request_address_change_date) : null;
            if($date_b) {
                $d = date('d', $date_b);
                $m = date('m', $date_b);
                $y = date('Y', $date_b);
            } else {
                $d = $m = $y = null;
            }
            

        ?>

		<table  width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
					<td width="15%" height="30" style="border: 0;">The Date of Change of Address </td>
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
                    <td width="30%" height="22"  style="border:0"   ></td>
                    <td width="10%" style="border:0"    > </td>
                    <td colspan="2" class="bg-color">
                        <center>Day</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="2" class="bg-color" >
                        <center>Month</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="4" class="bg-color" >
                        <center>Year</center>
                    </td>
                </tr>
			</tbody>
		</table> 
        
        <br><br><br><br>


		<table style="border: 0;" width="100%" autosize="1" >
                <tbody>

                   <?php

                    if(count($sec_changes)) { 
                        foreach($sec_changes as $sec) {
                            ?>

                    <tr>
                        <td width="153pt"  height="100px" align="left" class="bg-color">Full name of Authorized representative </td>
                        <td width="357pt"><?php echo $sec['full_name'];?></td>
                    
                    </tr>

                    <tr> <td style="border:0"></td></tr>
                    <tr>
                        <td width="153pt" height="100px"  align="left" class="bg-color">Signature of Authorized representative</td>
                        <td width="357pt">&nbsp;</td>
                    
                    </tr>

                            <?php 


                        }

                    } else { 
                        ?>
                    <tr>
                        <td width="153pt"  height="100px" align="left" class="bg-color">Full name of Authorized representative </td>
                        <td width="357pt">&nbsp;</td>
                    
                    </tr>

                    <tr> <td style="border:0"></td></tr>
                    <tr>
                        <td width="153pt" height="100px"  align="left" class="bg-color">Signature of Authorized representative</td>
                        <td width="357pt">&nbsp;</td>
                    
                    </tr>

                        <?php 
                    }

                    ?>
                    



                </tbody>
            </table>
        
        <br><br>

        <?php
           // $date =  strtotime($date_of_record);
            $date = time();
            if($date) {
                $d = date('d', $date);
                $m = date('m', $date);
                $y = date('Y', $date);
            } else {
                $d = $m = $y = null;
            }
            

        ?>

		<table  width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
					<td width="15%" height="30" style="border: 0;">Date</td>
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
                    <td width="30%" height="22"  style="border:0"   ></td>
                    <td width="10%" style="border:0"    > </td>
                    <td colspan="2" class="bg-color">
                        <center>Day</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="2" class="bg-color" >
                        <center>Month</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="4" class="bg-color" >
                        <center>Year</center>
                    </td>
                </tr>
			</tbody>
		</table> 
        
        <br>

 
 <table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
                <tbody>

                <tr>
                    <td style="border:0;"><b>Presented by:</b></td>
                </tr>
                
                <tr> 
                    <td width="82pt" height="40" class="bg-color">Full Name </td>
                    <td width="458pt" height="40"><?php echo $loginUser->first_name; ?> <?php echo $loginUser->last_name; ?></td>
                  <!--  <td width="30pt"  height="40" rowspan="6" style="text-rotate: 90;" align="center" class="bg-color">Signature</td>
                    <td width="168pt" height="40"   rowspan="6"  ></td> -->
                </tr>

                <tr >
                    <td width="82pt"  class="bg-color"  height="40">E Mail Address</td>
                    <td width="229pt"><?php echo $loginUser->email; ?></td>
                </tr>

                <tr >
                    <td width="82pt" class="bg-color"  height="40">Telephone No</td>
                    <td width="229pt"><?php echo $loginUser->telephone; ?></td>
                </tr>

                <tr >
                    <td width="82pt" class="bg-color"  height="40">Mobile No</td>
                    <td width="229pt" ><?php echo $loginUser->mobile; ?></td>
                </tr>

                <!--<tr >
                    <td width="82pt" class="bg-color"  height="40">Address</td>
                    <td width="229pt">  <?php // echo $loginUserAddress->address1.',<br/>'; ?>
                            <?php // echo ( $loginUserAddress->address2 ) ? $loginUserAddress->address2.',<br/>' : ''; ?>
                            <?php // echo $loginUserAddress->city.',<br/>'; ?>
                            <?php // echo '<strong>postcode: </strong>'.$loginUserAddress->postcode; ?></td>
                </tr> -->
                </tbody>
        </table>
		  
    </section>
    </body>
</html>