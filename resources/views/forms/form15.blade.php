info_array<html>
    <head>
        <style>
           @page {
	header: page-header;
	footer: page-footer;
        }
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
                font-family: 'SegoeUI', sans-serif;
            }
            table{
                page-break-inside: avoid;
                table-layout:fixed;
            }

            font {
                margin-left: 0px;
                margin-right: 0px;
                font-size: 14px;
                font-family: 'SegoeUI', sans-serif;
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

            /* .a> h6 {
                float: left;
                transform: rotate(-90deg);
                -webkit-transform: rotate(-90deg);  
                -ms-transform: rotate(-90deg); 
        

            } */

            body {
                /* margin-left: 20px; */
                font-family: 'SegoeUI', sans-serif;

            }
        </style>
    </head>

    <?php

      $isGURANTEE = ( $companyType->key =='COMPANY_TYPE_GUARANTEE_32' ||  $companyType->key =='COMPANY_TYPE_GUARANTEE_34' );
      $pv_first = '';
      $pv_second = '';
      $pv_number_part = '';
      if($certificate_no){
        $pv_first =  substr($certificate_no,0,1);
        $pv_second =  substr($certificate_no,1,1);
        $pv_number_part =  substr($certificate_no,2);
      }
    ?>

    <body>
    <section class="form-body">
        <header class="form-header">
            <table  width="100%" autosize="1" style="border:0; padding:0;">
                <tr>
                    <td width="10%" style="border:0; padding:0px;" >
                  
                    <img width="100" height="100" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo">
                </td>
                    <td width="67%" style="border:0; font-size: 20px; padding-top:20px; padding-left:105px " align="center"><b>FORM 15<br></b></td>
                    <td width="13%" style="border:0; padding:0px; font-size: 12px;" align="left">(Section 131(1))</td>
                    <td width="10%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
                </tr>
                <tr>
                    <td colspan="4" align="center" style="border:0; font-size:15px; padding:0;"><b>Annual Return of A Company</b></td>
                </tr>
                <tr>
                    <td colspan="4" align="center" style="border:0; font-size:15px; padding:0;" >
                    <b> 
                        <?php if($isGURANTEE) { ?>
                            (Limited by Guarantee)
                        <?php } else { ?>
                            (other than a Company Limited by Guarantee)
                        <?php } ?>
                       
                    </b>
                </td>
                </tr>
                <tr>
                    <td colspan="4"  style="border:0; padding:0px; font-size:13px; padding-left:230px;">

                    <?php if($isGURANTEE) { ?>
                        (Pursuant to Section 131(1)  read with Section 35(1) - Companies Act No. 7 2007)
                        
                    <?php }else{ ?>
                        (Pursuant to Section 131(1) - Companies Act No. 7 2007)
                    <?php } ?>
                   
                </td>
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

            <table  width="100%" autosize="1" >
                <tbody>
                    <tr>
                        <td width="25%" height="50" class="bg-color">Name of the Company </td>
                        <td width="75%" height="50"><?php echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?></td>
                    </tr>
                </tbody>
            </table>
            <br>

            <?php
            $incorporated_at = $company_info->incorporation_at ? strtotime($company_info->incorporation_at) : null;
            if($incorporated_at) {
                $d = date('d', $incorporated_at);
                $m = date('m', $incorporated_at);
                $y = date('Y', $incorporated_at);
            } else {
                $d = $m = $y = null;
            }
            

            ?>

             <table  width="100%" autosize="1" height="50.4" style="border:0">
            <tbody>
                <tr>
                    <td height="30" align="right" style="border:0">Date of Incorporation :</td>
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
                    <td colspan="2" class="bg-color" >
                        <center>Day</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="2" class="bg-color">
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


            <table  width="100%" autosize="1" >
                <tbody>
                    <tr>
                        <td width="25%" height="50" class="bg-color">Former Name of Company(if any)  </td>
                        <td width="75%" height="50">
                            
                            <?php echo $latest_name_change['oldName']; ?><?php  echo  $latest_name_change['oldName'] ? ' '. $latest_name_change['old_postfix'] : '' ?>
                        
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>

            <table  width="100%" autosize="1" >
                <tbody>
                    <tr>
                        <td width="25%" height="50" class="bg-color">Registered Office Address</td>
                        <td width="75%" height="auto">
                          <?php

                          echo  isset($company_address->address1) && $company_address->address1 ? $company_address->address1.',<br/>' : '';
                          echo  isset($company_address->address2) && $company_address->address2 ? $company_address->address2.',<br/>' : '';
                          echo  isset($company_address->city) && $company_address->city ? $company_address->city.'.<br/>' : '';
                          echo  isset($company_address->postcode) && $company_address->postcode ? '<strong>post code:</strong>'.$company_address->postcode : '';
                          ?>
                           
                           
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>

             <table   width="100%" autosize="1" height="50.4" style="border:0">
                <tbody>
                <?php
            $annual_return_date = $dates['this_year_annual_return_date'] ? strtotime($dates['this_year_annual_return_date']) : null;
            if($annual_return_date) {
                $d = date('d', $annual_return_date);
                $m = date('m', $annual_return_date);
                $y = date('Y', $annual_return_date);
            } else {
                $d = $m = $y = null;
            }

            ?>
            
                    <tr>

                        <td height="30" align="right" style="border:0">Date of Annual Return:</td>
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
                        <td colspan="2" class="bg-color" >
                            <center>Day</center>
                        </td>
                        <td width="10%" style="border:0"    ></td>
                        <td colspan="2" class="bg-color">
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
               

                <tr>
                    <td style="border:0;" colspan="2"><b><?php echo ($isGURANTEE) ? 'MEMBER REGISTER' : 'SHARE REGISTER'; ?></b></td>
                </tr>
                <tr>
                    <td width="30%" align="center">Description of part Registers </td>
                    <td width="70%" align="center">Address of place where kept </td>
                <tr>
                <?php if(count($share_register)) {
                    foreach($share_register as $sr) { ?>

                    <tr>
                    <td width="40%" height="30"><?php

                        $arr = $sr['description'];
                        if(is_array($arr) && count($arr)) {
                            foreach($arr as $a ) {
                                echo $a.'<br/>';
                            }
                        }
                    // echo $sr['description']; 
                     
                     ?></td>
                    <td width="60%" height="30">
                        <?php
                             echo  isset($sr['localAddress1']) && $sr['localAddress1'] ? $sr['localAddress1'].',<br/>' : '';
                             echo  isset($sr['localAddress2']) && $sr['localAddress2'] ? $sr['localAddress2'].',<br/>' : '';
                             echo  isset($sr['city']) && $sr['city'] ? $sr['city'].'.<br/>' : '';
                             echo  isset($sr['postcode']) && $sr['postcode'] ? '<strong>post code:</strong>'.$sr['postcode'] : '';
                        ?>

                    </td>
                   <tr>
                    
                  <?php }
                }else { ?>
                <tr>
                    <td width="40%" height="30">N/A</td>
                    <td width="60%" height="30">N/A</td>
                <tr>
                
                <?php }
                ?>
                
                
            </table>
            <br>

           

            <table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
                <tbody>

                <tr>
                    <td style="border:0;" colspan="2"><b>RECORDS:</b></td>
                </tr>
              
                <tr>
                    <td width="30%" align="center">Description of part Records </td>
                    <td width="70%" align="center">Address of place where kept </td>
                <tr>
                <?php if(count($annual_records)) {
                    foreach($annual_records as $sr) { ?>

                    <tr>
                    <td width="40%" height="30"><?php
                    //  $arr = $sr['description'];
                   //   if(is_array($arr) && count($arr)) {
                    //      foreach($arr as $a ) {
                    //          echo $a.'<br/>';
                    //      }
                   //   }
                     echo $sr['description']; 
                     
                     ?></td>
                    <td width="60%" height="30">
                        <?php
                             echo  isset($sr['localAddress1']) && $sr['localAddress1'] ? $sr['localAddress1'].',<br/>' : '';
                             echo  isset($sr['localAddress2']) && $sr['localAddress2'] ? $sr['localAddress2'].',<br/>' : '';
                             echo  isset($sr['city']) && $sr['city'] ? $sr['city'].'.<br/>' : '';
                             echo  isset($sr['postcode']) && $sr['postcode'] ? '<strong>post code:</strong>'.$sr['postcode'] : '';
                        ?>

                    </td>
                   <tr>
                    
                  <?php }
                }else { ?>
                <tr>
                    <td width="40%" height="30">N/A</td>
                    <td width="60%" height="30">N/A</td>
                <tr>
                
                <?php }
                ?>
            </table>
            <br>

            <?php if(!$isGURANTEE) { ?>
            

            <table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
               
                <tr>
                    <td style="border:0;" colspan="7"><b>SHARES:</b></td>
                </tr>
                <tr>
                    <td width="0.71in" align="center">Class of shares</td>
                    <td width="0.71in" align="center">Date and amount of each Issue of the series</td>
                    <td width="1.41in" align="center">Value/consideration for shares issued </td>
                    <td width="0.71in" align="center">Number of shares issued for cash  </td>
                    <td width="1.41in" align="center">Number of shares issued other than for cash </td>
                    <td width="0.71in" align="center">See note (i)</td>
                    <td width="1.41in" align="center">Amount called on shares </td>
                <tr>
                <?php 

                $total_shares_issued = 0;
                
                if(count($share_records)) {
                    foreach($share_records as $sr) { 

                       

                         $share_issued = floatval($sr['no_of_shares_as_cash']) +  floatval($sr['no_of_shares_as_non_cash']);
                         $total_shares_issued += $share_issued;

                       
                        
                        ?>

                    <tr>
                    <td width="0.71in" height="50"><?php echo $sr['selected_share_class_name']; ?></td>
                    <td width="0.71in" height="50">
                        <strong>Issued Date:</strong> <?php echo $sr['date_of_issue']; ?><br/>
                        <strong>Amount Issued:</strong> <?php echo $share_issued; ?>
                    </td>
                    <td width="1.41in" height="auto">
                       <?php
                           if( $sr['is_issue_type_as_cash'] == 'yes' ) {
                               ?>
                               <p><span style="font-weight: bold;">Share Value for Issued as Cash</span> : <?php echo floatval($sr['consideration_of_shares_as_cash']); ?> </p>

                               <?php 
                           }
                           if( $sr['is_issue_type_as_non_cash'] == 'yes' ) {
                            ?>
                            <br/>
                            <p><span style="font-weight: bold;">Consideration
for shares issued</span> :<br/> <?php echo $sr['consideration_of_shares_as_non_cash']; ?> </p>

                            <?php 
                            }


                        ?>

                    </td>
                    <td width="0.71in" height="50">
                         <?php echo $sr['is_issue_type_as_cash'] == 'yes'  ? floatval($sr['no_of_shares_as_cash']) : ''; ?>
                        
                    </td>
                    <td width="1.41in" height="50"> 
                    <?php echo $sr['is_issue_type_as_non_cash'] == 'yes'  ? floatval($sr['no_of_shares_as_non_cash']) : ''; ?>
                    </td>
                    <td width="0.71in" height="50">
                    <?php echo $sr['consideration_paid_or_provided']; ?>
                    </td>
                    <td width="1.41in" height="50">
                    <?php echo $sr['called_on_shares']; ?>
                    </td>
                <tr>


                    <?php }

                }else { ?>
                <tr>
                    <td width="0.71in" height="50">N/A</td>
                    <td width="0.71in" height="50">N/A</td>
                    <td width="1.41in" height="50">N/A</td>
                    <td width="0.71in" height="50">N/A</td>
                    <td width="1.41in" height="50">N/A</td>
                    <td width="0.71in" height="50">N/A</td>
                    <td width="1.41in" height="50">N/A</td>
                <tr>
                <?php } ?>
                <tr style="border-bottom:0">
                    <td width="0.71in" height="30">Total shares issued </td>
                    <td width="0.71in" height="30"><?php 

                    echo ($total_shares_issued) ? $total_shares_issued : '';
                    
                    
                    ?></td>
                    <td  height="30" style="border:0" colspan="5"><span style="font-size:12px;">Note : (i) Complete this column where the full consideration is not payable or required to be provided in respect of the issue of the share. Give the value of that part of the consideration paid or provided in respect of the issue of the share</span> </td>
                    <!-- <td width="10%" height="30" style="border:0"></td>
                    <td width="20%" height="30" style="border:0"> </td>
                    <td width="10%" height="30" style="border:0"></td>
                    <td width="20%" height="30" style="border:0"></td> -->
                <tr>
            </table>
            <br>
          

            <table  width="100%" autosize="1" >
                <tbody>
                    <tr>
                        <td width="40%" height="50" class="bg-color">Total amount of calls received </td>
                        <td width="60%" height="50"><?php echo $amount_calls_recieved; ?></td>
                    </tr>
                </tbody>
            </table>
            <br>

            <table  width="100%" autosize="1" >
                <tbody>
                    <tr>
                        <td width="40%" height="50" class="bg-color">Total amount of calls unpaid </td>
                        <td width="60%" height="50"><?php echo $amount_calls_unpaid; ?></td>
                    </tr>
                </tbody>
            </table>
            <br>

            <table  width="100%" autosize="1" >
                <tbody>
                    <tr>
                        <td width="40%" height="50" class="bg-color">Total number of shares forfeited</td>
                        <td width="60%" height="50"><?php echo $amount_calls_forfeited; ?></td>
                    </tr>
                </tbody>
            </table>
            <br>

            <table  width="100%" autosize="1" >
                <tbody>
                    <tr>
                        <td width="40%" height="50" class="bg-color">Total number  of shares purchased or otherwise acquired</td>
                        <td width="60%" height="50"><?php echo $amount_calls_purchased; ?></td>
                    </tr>
                </tbody>
            </table>
            <br>

            <table  width="100%" autosize="1" >
                <tbody>
                    <tr>
                        <td width="40%" height="50" class="bg-color">Total number of shares redeemed</td>
                        <td width="60%" height="50"><?php echo $amount_calls_redeemed; ?></td>
                    </tr>
                </tbody>
            </table>
            <br>
            <?php } ?>
            <br>

            <table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
                <tr>
                   <td style="border:0;" colspan="3"><b>DIRECTORS:</b></td>
               </tr>
                <tr>
                    <td width="30%" align="center" class="bg-color">Full Name</td>
                    <td width="35%" align="center" class="bg-color">NIC No or passport No (Specify Country)</td>
                    <td width="35%" align="center" class="bg-color">Residential Address </td>
                </tr>
                <?php if(count($directors)) {
                    foreach($directors as $d) { ?>

                    <tr>

                    <td width="30%" height="50"><?php echo ( $d['firstname'].' '.$d['lastname'] );?></td>
                    <td width="35%" height="50">
                        <?php 
                        if($d['country'] != 'Sri Lanka'){
                            echo 'Passport No: '. $d['passport'];
                            echo '<br/>';
                            echo 'Country: '. $d['passport_issued_country'];
                        }else{
                            echo  $d['nic'];  
                        }
                        ?>
                     </td>
                    <td width="35%" height="50">

                            <?php if ($d['type'] == 'local') {?>
                   
                            <?php echo $d['localAddress1'] . ',<br/>'; ?>
                            <?php echo ($d['localAddress2']) ? $d['localAddress2'] . ',<br/>' : ''; ?>
                            <?php echo $d['city'] . ',<br/>'; ?>
                            <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>
                            <br/>
                            <br/>
                        <?php if($d['forAddress1'] && $d['forCity'] && $d['forPostcode']) { ?>
                       <p style="text-decoration:underline">Foreign Address</p>
                        <?php } ?>
                            
                        <?php echo ($d['forAddress1']) ? $d['forAddress1'] . ',<br/>' : ''; ?>
                        <?php echo ($d['forAddress2']) ? $d['forAddress2'] . ',<br/>' : ''; ?>
                        <?php echo ($d['forCity']) ? $d['forCity'] . ',<br/>' : '' ; ?>
                        <?php echo ($d['forPostcode']) ? $d['forPostcode'].',<br/>' : ''; ?>
                        <?php
                        if($d['forAddress1'] && $d['forCity'] && $d['forPostcode']) {
                         echo ($d['country']) ? $d['country'] : ''; 
                        }
                         ?>

                        <?php } else {?>

                        <p style="text-decoration:underline">Local Address</p>

                        <?php echo $d['localAddress1'] . ',<br/>'; ?>
                        <?php echo ($d['localAddress2']) ? $d['localAddress2'] . ',<br/>' : ''; ?>
                        <?php echo $d['city'] . ',<br/>'; ?>
                        <?php // echo $d['district'] . ','; ?>
                        <?php // echo $d['province'] . ','; ?>
                        <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>

                        <br/>
                        <br/>
                        <p style="text-decoration:underline">Foreign Address</p>
                        <?php echo $d['forAddress1'] . ',<br/>'; ?>
                        <?php echo ($d['forAddress2']) ? $d['forAddress2'] . ',<br/>' : ''; ?>
                        <?php echo $d['forCity'] . ',<br/>'; ?>
                        <?php echo '<strong>zipcode: </strong>'.$d['forPostcode'].',<br/>'; ?>
                        <?php echo ($d['country']) ? $d['country'] : ''; ?>

                        <?php }?>
                    </td>
                    
                   <tr>
                    
                  <?php }
                }else { ?>
                <tr>
                    <td width="30%" height="50">N/A</td>
                    <td width="35%" height="50">N/A</td>
                    <td width="35%" height="50">N/A</td>
                <tr>
                
                <?php }
                ?>
                
            </table>
            <br>
            <br>

            <table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
                <tr>
                   <td style="border:0;" colspan="4"><b>SECRETARY / SECRETARIES :</b></td>
               </tr>
                <tr>
                    <td width="25%" align="center" class="bg-color">Full Name</td>
                    <td width="35%" align="center" class="bg-color">Residential / Registered / Principal Office Address</td>
                    <td width="20%" align="center" class="bg-color">Nationality</td>
                    <td width="20%" align="center" class="bg-color">Registration No</td>
                </tr>

                <?php if(count($secs) || count($secFirms)) {

                    if(count($secs)) {
                    foreach($secs as $d) { ?>
                <tr>
                    
                    <td width="25%" height="50"><?php echo ( $d['firstname'].' '.$d['lastname'] );?></td>
                    <td width="35%" height="50">
                                <?php  echo $d['localAddress1'].',<br/>'; ?>
                                <?php echo ($d['localAddress2']) ? $d['localAddress2'].',<br/>' : ''; ?>
                                <?php  echo $d['city'].',<br/>'; ?>
                                <?php // echo $d['postcode']; ?>
                                <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>
                    </td>
                    <td width="20%" height="50"><?php

                     echo isset($d['country']) && $d['country'] ? $d['country'] : 'Sri Lanka';
                    
                    ?></td>
                    <td width="20%" height="50"><?php

                    echo isset($d['regDate']) && $d['regDate'] ? $d['regDate'] : '';

                    ?></td>
                </tr>
                    <?php }
                    }

                    if(count($secFirms)) {
                        foreach($secFirms as $d) { ?>
                    <tr>
                        
                        <td width="25%" height="50"><?php echo $d['firm_name'] ;?></td>
                        <td width="35%" height="50">
                                    <?php  echo $d['firm_localAddress1'].',<br/>'; ?>
                                    <?php echo ($d['firm_localAddress2']) ? $d['firm_localAddress2'].',<br/>' : ''; ?>
                                    <?php  echo $d['firm_city'].',<br/>'; ?>
                                    <?php  echo '<strong>postcode: </strong>'.$d['firm_postcode']; ?>
                        </td>
                        <td width="20%" height="50"><?php
    
                         echo isset($d['firm_country']) && $d['firm_country'] ? $d['firm_country'] : 'Sri Lanka';
                        
                        ?></td>
                        <td width="20%" height="50"><?php
    
                        echo isset($d['pvNumber']) && $d['pvNumber'] ? $d['pvNumber'] : '';
    
                        ?></td>
                    </tr>
                        <?php }
                        }

                }else { ?>

                <tr>
                    
                    <td width="25%" height="50">N/A</td>
                    <td width="35%" height="50">N/A</td>
                    <td width="20%" height="50">N/A</td>
                    <td width="20%" height="50">N/A</td>
                </tr>

               <?php  } ?>
            </table>
            <br>

            
            <table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
                <tr>
                   <td style="border:0;" colspan="2"><b>AUDITORS :</b></td>
               </tr>
                <tr>
                    <td width="40%" align="center" class="bg-color">Full Name</td>
                    <td width="60%" align="center" class="bg-color">Address</td>
                </tr>
               

                <?php if(count($annual_auditors)) {
                    foreach($annual_auditors as $sr) { ?>

                    <tr>
                    <td width="40%" height="30"><?php echo $sr['first_name']; ?> <?php echo $sr['last_name']; ?></td>
                    <td width="60%" height="30">
                        <?php
                             echo  isset($sr['localAddress1']) && $sr['localAddress1'] ? $sr['localAddress1'].',<br/>' : '';
                             echo  isset($sr['localAddress2']) && $sr['localAddress2'] ? $sr['localAddress2'].',<br/>' : '';
                             echo  isset($sr['city']) && $sr['city'] ? $sr['city'].'.<br/>' : '';
                             echo  isset($sr['postcode']) && $sr['postcode'] ? '<strong>post code:</strong>'.$sr['postcode'] : '';
                        ?>

                    </td>
                   <tr>
                    
                  <?php }
                }else { ?>
                <tr>
                    <td width="40%" height="30">N/A</td>
                    <td width="60%" height="30">N/A</td>
                <tr>
                
                <?php }
                ?>
            </table>
            <br>
            <br>

            <span><b> LAST ANNUAL RETURN :</b></span>
            <br>
            <br>

            <?php 
                $is_incorporation_date_as_last_annual_return = $dates['is_incorporation_date_as_last_annual_return'] ? true : false;
                $last_year_annual_return_date = $dates['last_year_annual_return_date'] ? strtotime($dates['last_year_annual_return_date']) : null;
                
                if($is_incorporation_date_as_last_annual_return){

                    $incorporated_at = $company_info->incorporation_at ? strtotime($company_info->incorporation_at) : null;
                    if($incorporated_at) {
                        $d = date('d', $incorporated_at);
                        $m = date('m', $incorporated_at);
                        $y = date('Y', $incorporated_at);
                    } else {
                        $d = $m = $y = null;
                    }

                }else {
                    if($last_year_annual_return_date) {
                        $d = date('d', $last_year_annual_return_date);
                        $m = date('m', $last_year_annual_return_date);
                        $y = date('Y', $last_year_annual_return_date);
                    } else {
                        $d = $m = $y = null;
                    }
                }
                
               

            ?>

            <span style="font-size:13px">(a)	The date of the last annual return under the Companies Act No.17 of 1982 or the Companies Act No.7 of  2007 </span>
            <br>
            <br>

            

            <table width="100%" autosize="1" height="50.4" style="border:0">
            <tbody>

                <?php if(!$is_incorporation_date_as_last_annual_return) { ?>
                    <tr>
                     <td height="25" align="right" style="border:0"></td>
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
                
                
                <?php } else { ?>

                    <tr>
                    <td height="25" align="right" style="border:0"></td>
                    <td width="8%" style="border:0"    ></td>
                    <td width="5%"></td>
                    <td width="5%"></td>
                    <td width="10%"  style="border:0"   ></td>
                    <td width="5%"></td>
                    <td width="5%"></td>
                    <td width="10%" style="border:0"    ></td>
                    <td width="5%"></td>
                    <td width="5%"></td>
                    <td width="5%"></td>
                    <td width="5%"></td>
                    <td width="4%" style="border:0"    ></td>
                </tr>
                
                
                <?php } ?>
                
                <tr>
                    <td width="30%" height="22"  style="border:0"   ></td>
                    <td width="10%" style="border:0"    > </td>
                    <td colspan="2" class="bg-color" >
                        <center>Day</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="2" class="bg-color">
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
        <br>
        <br>
        <br>

        <span style="font-size:13px">(b)	In the case of the first annual return, please give the date of incorporation</span>
            <br>
            <br>

            <table  width="100%" autosize="1" height="50.4" style="border:0">
            <tbody>

             <?php if($is_incorporation_date_as_last_annual_return) { ?>

              <tr>
                     <td height="25" align="right" style="border:0"></td>
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

             <?php }else { ?>

                <tr>
                    <td height="25" align="right" style="border:0"></td>
                    <td width="8%" style="border:0"    ></td>
                    <td width="5%"></td>
                    <td width="5%"></td>
                    <td width="10%"  style="border:0"   ></td>
                    <td width="5%"></td>
                    <td width="5%"></td>
                    <td width="10%" style="border:0"    ></td>
                    <td width="5%"></td>
                    <td width="5%"></td>
                    <td width="5%"></td>
                    <td width="5%"></td>
                    <td width="4%" style="border:0"    ></td>
                </tr>
            
            <?php } ?>
                
                <tr>
                    <td width="30%" height="22"  style="border:0"   ></td>
                    <td width="10%" style="border:0"    > </td>
                    <td colspan="2" class="bg-color" >
                        <center>Day</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="2" class="bg-color">
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

        <br>
        <table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
            <tr>

             <?php if ($isGURANTEE) { ?>
                 
                <td style="border:0;" colspan="2">
                    <b>EXISTING MEMBERS :</b><br/>
                    <span style="font-size:10px">LIST  OF  PERSONS  WHO  ON  THE  FOURTEENTH  DAY  FROM  THE  DATE  OF  THE  FIRST  OR  ONLY  GENERAL  MEETING  IN  THE YEAR ARE MEMBERS OF THE COMPANY</span>
                </td>
                <?php }else { ?>
                    <td style="border:0;" colspan="3">
                    <b>EXISTING SHARE HOLDERS :</b><br/>
                    <span style="font-size:10px;">(LIST OF PERSONS WHO ON THE FOURTEENTH DAY FROM THE DATE OF THE FIRST OR ONLY GENERAL MEETING IN THE YEAR ARE SHARE HOLDERS OF THE COMPANY)   </span>
                  </td>
                <?php } ?>
                  
            </tr>
            <tr>
                <td width="30%" align="center" class="bg-color">Full Name</td>

                <?php if ($isGURANTEE) { ?>
                    <td width="70" align="center" class="bg-color">Residential Address</td>
                   
                <?php }else { ?>
                    <td width="40%" align="center" class="bg-color">Residential Address</td>
                    <td width="30%" align="center" class="bg-color">No of  shares</td>
                <?php } ?>
            </tr>

            <?php if(count($shareholders) || count($shareholderFirms)) {

                  if(count($shareholders)){
                      foreach($shareholders as $d ) {
                           ?>
                        <tr>
                            <td width="30%" height="50"><?php echo $d['firstname']; ?> <?php echo $d['lastname']; ?></td>
                            
                            <?php if ($isGURANTEE) { ?>

                                 <td width="70%" height="50">
                            <?php if ($d['type'] == 'local') {?>

                                <?php echo $d['localAddress1'] . ',<br/>'; ?>
                                <?php echo ($d['localAddress2']) ? $d['localAddress2'] . ',<br/>' : ''; ?>
                                <?php echo $d['city'] . ',<br/>'; ?>
                                <?php // echo $d['district'] . ','; ?>
                                <?php // echo $d['province'] . ','; ?>
                                <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>

                                <?php } else {?>

                                <?php echo $d['forAddress1'] . ',<br/>'; ?>
                                <?php echo ($d['forAddress2']) ? $d['forAddress2'] . ',<br/>' : ''; ?>
                                <?php echo $d['forCity'] . ',<br/>'; ?>
                                <?php echo '<strong>zipcode: </strong>'.$d['forPostcode'].',<br/>'; ?>
                                <?php echo ($d['country']) ? $d['country'] : ''; ?>

                                <?php }?>
                            </td>
                            

                            <?php }else { ?>

                                <td width="40%" height="50">
                            <?php if ($d['type'] == 'local') {?>

                                <?php echo $d['localAddress1'] . ',<br/>'; ?>
                                <?php echo ($d['localAddress2']) ? $d['localAddress2'] . ',<br/>' : ''; ?>
                                <?php echo $d['city'] . ',<br/>'; ?>
                                <?php // echo $d['district'] . ','; ?>
                                <?php // echo $d['province'] . ','; ?>
                                <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>

                                <?php } else {?>

                                <?php echo $d['forAddress1'] . ',<br/>'; ?>
                                <?php echo ($d['forAddress2']) ? $d['forAddress2'] . ',<br/>' : ''; ?>
                                <?php echo $d['forCity'] . ',<br/>'; ?>
                                <?php echo '<strong>zipcode: </strong>'.$d['forPostcode'].',<br/>'; ?>
                                <?php echo ($d['country']) ? $d['country'] : ''; ?>

                                <?php }?>
                            </td>
                            <td width="30%" height="50">
                            <?php echo isset( $d['shareRow']['no_of_shares']) && $d['shareRow']['no_of_shares'] ? $d['shareRow']['no_of_shares'] : ''; ?>
                            </td>
                            
                            
                            <?php } ?>
                        </tr>

                           <?php 
                      }
                  }

                  if(count($shareholderFirms)){
                    foreach($shareholderFirms as $d ) {
                         ?>
                      <tr>
                          <td width="30%" height="50"><?php echo $d['firm_name']; ?></td>

                            <?php if ($isGURANTEE) { ?>

                            <td width="70%" height="50">
                          

                          <?php echo $d['firm_localAddress1'] . ',<br/>'; ?>
                          <?php echo ($d['firm_localAddress2']) ? $d['firm_localAddress2'] . ',<br/>' : ''; ?>
                          <?php echo $d['firm_city'] . ',<br/>'; ?>
                          <?php echo '<strong>postcode: </strong>'.$d['firm_postcode']; ?>

                   
                      </td>

                            <?php }else{ ?>

                            <td width="40%" height="50">
                          

                                <?php echo $d['firm_localAddress1'] . ',<br/>'; ?>
                                <?php echo ($d['firm_localAddress2']) ? $d['firm_localAddress2'] . ',<br/>' : ''; ?>
                                <?php echo $d['firm_city'] . ',<br/>'; ?>
                                <?php echo '<strong>postcode: </strong>'.$d['firm_postcode']; ?>

                         
                            </td>
                            <td width="30%" height="50">
                            <?php echo isset( $d['shareRow']['no_of_shares']) && $d['shareRow']['no_of_shares'] ? $d['shareRow']['no_of_shares'] : ''; ?>
                            </td>
                            
                            <?php } ?>
                          
                          
                      </tr>

                         <?php 
                    }
                }


            }else { 
                ?>

                 <tr>
                    <td width="30%" height="50"></td>

                     <?php if ($isGURANTEE) { ?>

                      <td width="70%" height="50"></td>
                      
                     <?php }else { ?>

                         <td width="40%" height="50">N/A</td>
                         <td width="30%" height="50">N/A</td>
                     
                     <?php } ?>

                   
                </tr>

                <?php 
            }
            ?>
        </table> 
        <br>



       <table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
            <tr>

             <?php if ($isGURANTEE) { ?>
                 
                <td style="border:0;" colspan="3">
                    <b>PERSONS WHO HAVE CEASED TO BE MEMBERS SINCE THE LAST RETURN OR IN THE CASE OF THE FIRSTRETURN,  SINCE THE DATE OF INCORPORATION :</b>
                    <span style="font-size:10px;">If the names are not arranged in alphabetical order the return shall have annexed to it an index sufficient to enable the name of any person in such list to be readily found</span>
                </td>
                <?php }else { ?>
                    <td style="border:0;" colspan="4">
                    <b>PERSONS WHO HAVE CEASED TO HOLD SHARES SINCE THE LAST RETURN OR IN THE CASE OF THE FIRST RETURN,
      SINCE THE DATE OF INCORPORATION. :</b><br/>
                    <span style="font-size:10px;">If the names are not arranged in alphabetical order the return shall have annexed to it an index sufficient to enable the name of any person in such list to be readily found</span>
                  </td>
                <?php } ?>
                  
            </tr>
            <tr>
                <td width="25%" align="center" class="bg-color">Full Name</td>

                <?php if($isGURANTEE) { ?>
                <td width="55%" align="center" class="bg-color">Residential Address</td>
                <td width="20%" align="center" class="bg-color">Date of Cessation</td>
                
                <?php }else{  ?>
                <td width="35%" align="center" class="bg-color">Residential Address</td>
                <td width="20%" align="center" class="bg-color">No of shares held</td>
                <td width="20%" align="center" class="bg-color">Date of Registration of Transfer</td>

                <?php  } ?>
               
            </tr>
           

           <?php if($isGURANTEE) {
            if(count($shareholders_inactive) || count($shareholderFirms_inactive)) {

                    if(count($shareholders_inactive)){
                        foreach($shareholders_inactive as $d ) {
                            ?>

                             <tr>
                                <td width="25%" height="40">
                               
                                <?php echo $d['firstname']; ?> <?php echo $d['lastname']; ?>
                               </td>
                                <td width="55%" height="40">
                                <?php if ($d['type'] == 'local') { ?>
                                    <?php echo $d['localAddress1'] . ',<br/>'; ?>
                                    <?php echo ($d['localAddress2']) ? $d['localAddress2'] . ',<br/>' : ''; ?>
                                    <?php echo $d['city'] . ',<br/>'; ?>
                                    <?php // echo $d['district'] . ','; ?>
                                    <?php // echo $d['province'] . ','; ?>
                                    <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>

                                <?php } else {?>

                                    <?php echo $d['forAddress1'] . ',<br/>'; ?>
                                    <?php echo ($d['forAddress2']) ? $d['forAddress2'] . ',<br/>' : ''; ?>
                                    <?php echo $d['forCity'] . ',<br/>'; ?>
                                    <?php echo '<strong>zipcode: </strong>'.$d['forPostcode'].',<br/>'; ?>
                                    <?php echo ($d['country']) ? $d['country'] : ''; ?>

                                <?php } ?>
                                </td>
                                <td width="20%" height="40"><?php echo $d['date'];?></td>
                            </tr>
                        

                            <?php 
                        }
                    }

                    if(count($shareholderFirms_inactive)){
                    foreach($shareholderFirms_inactive as $d ) {
                        ?>
                        <tr>
                            <td width="25%" height="40"><?php echo $d['firm_name']; ?></td>
                            <td width="55%" height="40">
                                <?php echo $d['firm_localAddress1'] . ',<br/>'; ?>
                                <?php echo ($d['firm_localAddress2']) ? $d['firm_localAddress2'] . ',<br/>' : ''; ?>
                                <?php echo $d['firm_city'] . ',<br/>'; ?>
                                <?php echo '<strong>postcode: </strong>'.$d['firm_postcode']; ?>
                            </td>
                            <td width="20%" height="40"><?php echo $d['firm_date'];?></td>
                        </tr>

                        <?php 
                    }
                    }
                  } else {
                       ?>
                       <tr>
                        <td width="25%" height="40">N/A</td>
                        <td width="55%" height="40">N/A</td>
                        <td width="20%" height="40">N/A</td>
                        </tr>
                       <?php 
                  }
                    
                }else { 

                  
                    

                    if( ( isset($form9_records_list[0]) && count($form9_records_list[0]) )  || ( isset($shareholder_transfer_records[0]) && count($shareholder_transfer_records[0]) ) ) {

                        if(count($form9_records_list )) {
                            foreach($form9_record_list as $d) {
                                ?>
                                <tr>
                                <td width="25%" height="40"><?php echo  $d['full_name'];?></td>
                                <td width="35%" height="40"><?php echo  $d['address'];?></td>
                                <td width="20%" height="40"><?php echo $d['shares_held'];?> (<?php echo $d['aquire_or_redeemed'] ?>)</td>
                                <td width="20%" height="40"><?php echo $d['share_transfer_date'];?></td>

                                </tr>

                                <?php 
                            }
                        }

                        if(count($shareholder_transfer_records )) {
                            foreach($shareholder_transfer_records as $d) {
                                ?>
                                <tr>
                                <td width="25%" height="40"><?php echo $d['full_name'];?></td>
                                <td width="35%" height="40"><?php echo $d['address'];?></td>
                                <td width="20%" height="40"><?php echo  $d['shares_held'];?></td>
                                <td width="20%" height="40"><?php echo $d['share_transfer_date'];?></td>
                            </tr>

                                <?php 
                            }
                        }

                    } else {
                         ?>
                        <tr>
                            <td width="25%" height="40">N/A</td>
                            <td width="35%" height="40">N/A</td>
                            <td width="20%" height="40">N/A</td>
                            <td width="20%" height="40">N/A</td>
                        </tr>

                         <?php 
                    }
                   

                }
                ?>

            <tr>

            <?php if ($isGURANTEE) { ?>
                
            <td style="border:0;" colspan="3">
            <span style="font-size:10px;">Please ANNEX a list of shareholders in compliance with the requirements setout in the item (b) of the Fifth Schedule to the Act.</span>
            </td>
            <?php }else { ?>
                <td style="border:0;" colspan="4">
                <span style="font-size:10px;">Please ANNEX a list of shareholders in compliance with the requirements setout in the item (b) of the Fifth Schedule to the Act.</span>
                </td>
            <?php } ?>
                
            </tr>
        </table>
       
         <br>
         <br>
    
        <table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
            <tr>
                <td style="border:0;" colspan="4">
                   <b>CHARGES  INDEBTEDNESS :</b><br/>
                   <span style="font-size:13px;">(all charges required to be registered under section 102)</span>
                </td>
            </tr>
            <tr>
                <td width="25%" align="center" class="bg-color">Date & Description of instrument creating charges</td>
                <td width="10%" align="center" class="bg-color">Amount</td>
                <td width="20%" align="center" class="bg-color">Name of persons entitled to the charges</td>
                <td width="45%" align="center" class="bg-color">Address of persons entitled to the charges</td>
            </tr>

            <?php if(count($charges_recods_list)) {
                    foreach($charges_recods_list as $sr) { ?>

                    <tr>
                <td width="25%" height="50" valign="top">

                     <strong style="font-size:16px; text-decoration: underline;">Date:</strong>:<?php echo $sr['date'];?><br/><br/>
                    <strong style="font-size:16px; text-decoration: underline;">Description:</strong>
                    <p><?php echo $sr['description']; ?></p>



                </td>
                <td width="10%" height="50" valign="top"> 
                <?php echo $sr['amount']; ?>
                </td>
                <td width="20%" height="50" valign="top">
                <?php echo $sr['persons']; ?>

                </td>
                <td width="45%" height="50" valign="top"> <?php echo $sr['person_addresses']; ?></td>
            </tr>


                    <?php }
            }else { ?>

            <tr>
                <td width="25%" height="50">N/A</td>
                <td width="10%" height="50">N/A</td>
                <td width="20%" height="50">N/A</td>
                <td width="45%" height="50">N/A</td>
            </tr>
            
        
            <?php } ?>
            
            
        </table>
        <br>

    

        <table style="--primary-text-color: #212121; border:0;" width="100%"  height="50.4" autosize="1">
            <tr>
                <td style="border:0;" colspan="13">
                   <b>ANNUAL  GENERAL MEETING / RESOLUTION IN LIEU THEREOF :</b><br/>
                   <span style="font-size:12px;">Give below the date of the last annual general meeting of the Company held under the Companies Act (2007) or, if the company has done everything required to be done at the meeting by passing a resolution under section 144(3) of the Act, in lieu of holding the Annual General Meeting the date on which the resolution was passed </span>
                </td>
            </tr>

         <?php
           $meeting_label = '';
            if($meeting_type === 'Annual General Meeting') {
                $resolution_date = $resolution_date;
                $meeting_label = 'Date of General Meeting';
            }
            if($meeting_type === 'Resolution in Liue Thereof') {
                $resolution_date = $resolution_inlieu_date;
                $meeting_label = 'Resolution in Liue Thereof';
            }

            $resolution_date = $resolution_date ? strtotime($resolution_date) : null;
            if($resolution_date) {
                $d = date('d', $resolution_date);
                $m = date('m', $resolution_date);
                $y = date('Y', $resolution_date);
            } else {
                $d = $m = $y = null;
            }
            

            ?>
            <tbody>
            <tr>
                    <td height="30" align="right" style="border:0"><?php echo $meeting_label;?> :</td>
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
                    <td colspan="2" class="bg-color" >
                        <center>Day</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="2" class="bg-color">
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

        <?php if($isGURANTEE) {  ?>



        <table style="--primary-text-color: #212121; border:0;" width="100%"  height="50.4" autosize="1">
            <tr>
                <td style="border:0;" colspan="3">
                   <b>SIGNATURE OF A DIRECTOR AND THE SECRETARY :</b>
                 
                </td>
            </tr>

            <tr>
                <td width="45%" align="center" class="bg-color">Full Name</td>
                <td width="15%" align="center" class="bg-color">Position</td>
                <td width="40%" align="center" class="bg-color">Signature</td>
            </tr>
            <?php 
          //   if( $companyType->key !='COMPANY_TYPE_PRIVATE') { 
                if(count($signed_directors)) {
                    foreach($signed_directors as $d) {

                        if(!$d['saved']) {
                            continue;
                        }
                        ?>

                         <tr>
                            <td width="45%" height="50"><?php echo ( $d['first_name'].' '.$d['last_name'] );?></td>
                            <td width="15%" height="50" align="center" class="bg-color">Director</td>
                            <td width="40%" height="50"></td>
                        </tr>
                     <?php 

                    }

                }
                if(count($signed_secs)) {
                    foreach($signed_secs as $d) {

                        if(!$d['saved']) {
                            continue;
                        }
                        ?>
                          <tr>
                            <td width="45%" height="50"><?php echo ( $d['first_name'].' '.$d['last_name'] );?></td>
                            <td width="15%" height="50" align="center" class="bg-color">Secretary</td>
                            <td width="40%" height="50"></td>
                        </tr>

                      <?php 

                    }
                }

                if(count($signed_sec_firms)) {
                    foreach($signed_sec_firms as $d) {

                        if(!$d['saved']) {
                            continue;
                        }?>
                          <tr>
                            <td width="45%" height="50"><?php echo $d['name'];?></td>
                            <td width="15%" height="50" align="center" class="bg-color">Secretary</td>
                            <td width="40%" height="50"></td>
                        </tr>

                      <?php 

                    }
                }

           //  }
             ?>

        </table>
        <br>

        <?php } ?>

        <?php if(!$isGURANTEE) {  ?>


                <table style="--primary-text-color: #212121; border:0;" width="100%"  height="50.4" autosize="1">

                    <tr>
                        <td style="border:0;" colspan="2">
                        <b>DECLARATION UNDER SECTION 132 (a) OF THE COMPANIES ACT NO. 7 OF 2007 BY THE DIRECTORS OF A PRIVATE COMPANY :</b><br/>
                        <span style="font-size:12px;">I/ we  declare that to the best of my/our  knowledge and belief I/ we have done all things required to be done by me/us under  the Companies Act No. 7 of 2007.</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="border:0;" colspan="2">
                        <b>SIGNATURE OF DIRECTORS</b>
                        
                        </td>
                    </tr>
                    <tr>
                        <td width="60%" class="bg-color">Full Name</td>
                        <td width="40%" class="bg-color">Signature</td>
                    </tr>
                    <?php 

                        if(count($directors_on_declaration)) {
                                foreach($directors_on_declaration as $d) { ?>

                                <tr>
                                    <td width="60%"  height="50"><?php echo ( $d['first_name'].' '.$d['last_name'] );?></td>
                                    <td width="40%"  height="50"></td>
                                </tr>

                                <?php 
                                    
                                }
                        } else { ?>

                        <tr>
                        <td width="60%"  height="50"></td>
                        <td width="40%"  height="50"></td>
                        </tr>

                    <?php  }
                    ?>
                    
                </table>
                <br>

                <b>&nbsp;&nbsp;CERTIFICATE UNDER SECTION 132 (b) OF THE COMPANIES ACT NO. 7 OF 2007 BY A DIRECTOR AND 
            THE SECRETARY/SECRETARIES OF A PRIVATE COMPANY</b>
            <br>
            <br>

            <span style="font-size:10px;">We do hereby certify –</span>
            <br>
            <br>
            

            <table  width="100%" autosize="1" style="width:100%;">
                    <tr>
                        <td> that the company has not since the date of incorporation of the company issued any invitation to the public to subscribe
                            to any shares or debentures of the company.
                            that the company has not since the date of the last return, issued any invitation to the public to subscribe for any shares or debentures of the company</td>
                    </tr>
            </table>
            <br>

            <table  width="100%" autosize="1">
                    <tr>
                        <td> that the number of shareholders of the company does not exceed fifty.
                            that the number of the shareholders of the company exceed fifty but the excess consists wholly of persons who   
                            under section 27 are not be taken into account in relation to that limit.</td>
                    </tr>
            </table>
            <br>

            <table width="100%" autosize="1">
                    <tr>
                        <td width="45%" align="center" class="bg-color">Full Name</td>
                        <td width="15%" align="center" class="bg-color">Position</td>
                        <td width="40%" align="center" class="bg-color">Signature</td>
                    </tr>

                    <?php 
                   // if( $companyType->key =='COMPANY_TYPE_PRIVATE') { 
                        if(count($signed_directors)) {
                            foreach($signed_directors as $d) {

                                if(!$d['saved']) {
                                    continue;
                                }
                                
                                ?>

                                <tr>
                                    <td width="45%" height="50"><?php echo ( $d['first_name'].' '.$d['last_name'] );?></td>
                                    <td width="15%" height="50" align="center" class="bg-color">Director</td>
                                    <td width="40%" height="50"></td>
                                </tr>
                            <?php 

                            }

                        }
                        if(count($signed_secs)) {
                            foreach($signed_secs as $d) { 

                                if(!$d['saved']) {
                                    continue;
                                }
                                
                                ?>
                                <tr>
                                    <td width="45%" height="50"><?php echo ( $d['first_name'].' '.$d['last_name'] );?></td>
                                    <td width="15%" height="50" align="center" class="bg-color">Secretary</td>
                                    <td width="40%" height="50"></td>
                                </tr>

                            <?php 

                            }
                        }

                        if(count($signed_sec_firms)) {
                            foreach($signed_sec_firms as $d) { 
                                
                                if(!$d['saved']) {
                                    continue;
                                }
                                ?>
                                <tr>
                                    <td width="45%" height="50"><?php echo $d['name'];?></td>
                                    <td width="15%" height="50" align="center" class="bg-color">Secretary</td>
                                    <td width="40%" height="50"></td>
                                </tr>

                            <?php 

                            }
                        }

                 //   }
                    ?>
                
            </table>
                <br>

               

     <?php } ?>

     
        <table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
                <tbody>

                <tr>
                    <td style="border:0;"><b>Presented by:</b></td>
                </tr>
                
                 <tr >
                    <td width="112pt"  class="bg-color"  height="40">Full name</td>
                    <td width="357pt"><?php  echo $loginUser->first_name; ?> <?php echo $loginUser->last_name; ?></td>
                </tr>

                <tr >
                    <td width="112pt"  class="bg-color"  height="40">E Mail Address</td>
                    <td width="357pt"><?php echo $loginUser->email; ?></td>
                </tr>

                <tr >
                    <td width="112pt" class="bg-color"  height="40">Telephone No</td>
                    <td width="357pt"><?php echo $loginUser->telephone; ?></td>
                </tr>

                <tr >
                    <td width="112pt" class="bg-color"  height="40">Mobile No</td>
                    <td width="357pt" ><?php echo $loginUser->mobile; ?></td>
                </tr>

                <tr >
                    <td width="112pt" class="bg-color"  height="40">Address</td>
                    <td width="357pt">
                    <?php  echo $loginUserAddress->address1.',<br/>'; ?>
                            <?php  echo ( $loginUserAddress->address2 ) ? $loginUserAddress->address2.',<br/>' : ''; ?>
                            <?php  echo $loginUserAddress->city.',<br/>'; ?>
                            <?php  echo '<strong>postcode: </strong>'.$loginUserAddress->postcode; ?>
                    </td>
                </tr>
                </tbody>
            </table>






                <br>



    
    </body>

</html>