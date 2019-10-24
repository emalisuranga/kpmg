<html>
<title>FORM 05</title>
<head>
<meta charset="UTF-8">
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
            ;
            padding: 5px;
            font-family: sans-serif;
        }
        table{
                page-break-inside: avoid;
        }
    tr    { page-break-inside:avoid; page-break-after:auto }
    thead { display:table-header-group }
    tfoot { display:table-footer-group }
      
        font {
            margin-left: 0px;
            margin-right: 0px;
            font-size: 16px;
            font-family: sans-serif;
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
            margin-left: 1.95cm;
            font-family: sans-serif;

        }
    </style>
    
</head>

<body>
    <section class="form-body">
        <!-- {{ $foo }} -->
        <table width="100%" style="border:0; padding:0;">
            <tr>
                <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                <td width="52%" style="border:0; padding-left:160px; padding-top:65px; font-size: 18px;"><span><b>FORM 5</b></span><p>&nbsp;</p><p style="font-size: 13px; ">Application for </p></td>
                <td width="8%" style="border:0; padding:-1px; font-size: 10px;" align="left">(Section 32)</td>
                <td width="20%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>
            <tr>
                <td colspan="4" align="center" style="border:0; font-size:16px; padding:0;"><b>FOR INCORPORATION OF A COMPANY LIMITED BY GUARANTEE</b> </td>
            </tr>
            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">Section 32 of Companies Act No.
                    7 of 2007 (“the Act”)</td>
            </tr>
        </table>
        
        <br>
    

        <table style="border: 0;" width="100%">
            <tbody>
                <tr height="50">
                    <td width="25%" style="border: 0; "> Number of the <br>Company </td>
                    <td width="7%">&nbsp;</td>
                    <td width="7%">&nbsp;</td>
                    <td width="61%">&nbsp;</td>
                </tr>
            </tbody>
        </table>
        <br>
        <br>

        <table style="--primary-text-color: #212121; " width="100%">
            <tbody>
                <tr>
                    <td width="25%"  class="bg-color"><br>Name <br>Approval No.
                    </td>
                    <td width="75%"  ><?php echo $company_info->id; ?></td>
                </tr>
                <tr>
                    <td width="25%" style="padding:0; padding-left:5px;" class="bg-color">Name of <br>Proposed<br>
                        Company</td>
                    <td width="75%"><?php echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?></td>
                </tr>
                <tr>
                    <td width="25%"  class="bg-color">Registered Address<br>
                        <span style="font-size: 12px; padding:0px;"><I>(Physical Address in Sri Lanka and must not be a PO Box
                                Address)</I></span>
                    </td>
                    <td width="75%" height="80">

                        <?php

                     //   $line1 = $company_address['address1'];
                     //   $line2 = ($company_address['address2']) ? $company_address['address2'].',' : '' ;
                    //    $city = $company_address['city'];
                     //   $district = $company_address['district'];
                    //    $province = $company_address['district'];
                     //   $post_code = $company_address['postcode'];

                        $line1 = $company_address['address1'].',<br/>';
                        $line2 = ($company_address['address2']) ? $company_address['address2']. ',<br/>' : '';
                       // $city = $company_address['city']. ',<br/>';
                        $city = '';
                        $district = $company_address['district'];
                        $province = $company_address['district'];
                        $post_code = '<strong>postcode: </strong>'.$company_address['postcode'];

                        echo $line1.$line2.$city.$post_code;

                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>

        <table style="border: 0;" width="100%">
            <tbody>
                <tr>
                    <td colspan="2" style="border: 0; color:#000000">
                        <b><u>ARTICLES OF ASSOCIATION</u></b>
                    </td>
                </tr>
                <tr>
                    <td width="7%" style="border: 0; "></td>
                    <td  width="93%" style="border: 0;font-size: 14px; text-align:justify; padding-bottom:10px;">
                        <ul>
                            <li >The Articles of Association of the proposed company shall be
                                as set out in the
                                Annexure “A” signed by each of us.<br><br></li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>

                  <?php  
         
         $dc=0;
         foreach($directors as $d ){ $dc++; ?>
            <table style="border: 1" width="100%" autosize="1">
                <tbody>
                <?php if ($dc == 1) {?>
                <tr style="border-bottom: 0;" >
                    <td width="100%" style="width: 509pt; border-bottom; border:1px solid #000;border-top:1px solid #000; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; text-align:justify;" colspan="5">
                        <b>
                            <u style="font-size:15px"><br>INITIAL DIRECTORS</u>
                        </b><br/>
                        <span style="font-size: 14px; ">
                            The following persons are the initial directors of
                        the proposed company and
                        signify their consent by signing below and certify that each one
                        of them is not disqualified from being appointed/holding office*
                        as a director of a company:
                        </span>
                    </td>

                </tr>
                <?php } ?>
                <tr style="border-bottom: 0;" >
                    <td width="3.3%" height="0.5cm" rowspan="2" style="border-bottom: 0; "> </td>
                    <td width="21.4%" height="0.5cm" rowspan="2" class="bg-color">Full Name </td>
                    <td width="39.2%" height="0.5cm" rowspan="2"><?php echo ( $d['firstname'].' '.$d['lastname'] );?></td>
                    <td width="36.1%" height="0.5cm" colspan="2" style=" border-bottom: 0" class="bg-color">
                        <center>Email Address</center>
                    </td>
                </tr>
                <tr style="border-bottom: 0;">
                    <td colspan="2" height="30"  style="text-align:center;"><?php echo  $d['email']; ?></td>
                </tr>

                <tr style="border: 0;" >
                    <td width="3.3%" height="40" style="border-bottom: 0; border-top: 0;"> <?php echo $dc; ?> </td>
                    <td width="21.4%" height="40"  class="bg-color">NIC No. /PP No. & <br>Country if
                        Foreigner</td>
                    <td width="39.2%" height="40">
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
                    <!-- <td width="5%" style="border: 1; transform: rotate(270deg) translate(-5%, -20%); position: relative; vertical-align:middle; transform-origin:0 0;" rowspan="2"> Signature</td> -->
                    <td width="5%"height="40"  class="a" rowspan="2"  style="padding-left:10px;" >
                    <img width="20" height="80" src="{{  URL::to('/') }}/images/signature.png" alt="signature"></td>
                    <td width="36.1%" height="40" rowspan="2" style="border-bottom: 0;"></td>

                </tr>
                <tr style="border-top: 0;" >
                    <td width="3.3%" height="60" style="border-top: 0;"> </td>
                    <td width="21.4%"height="60" class="bg-color"> Residential Address</td>
                    <td width="39.2%" height="60"> 

                              <?php if ($d['type'] == 'local') {?>
                   
                    <?php echo $d['localAddress1'] . ',<br/>'; ?>
                    <?php echo ($d['localAddress2']) ? $d['localAddress2'] . ',<br/>' : ''; ?>
                    <?php // echo $d['city'] . ',<br/>'; ?>
                    <?php //echo $d['district'] . ','; ?>
                    <?php //echo $d['province'] . ','; ?>
                    <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>
                    <br/>
                    <br/>
                    <?php if($d['forAddress1'] && $d['forCity'] && $d['forPostcode']) { ?>
                        <p style="text-decoration:underline">Foreign Address</p>
                    <?php } ?>
                     
                    <?php echo ($d['forAddress1']) ? $d['forAddress1'] . ',<br/>' : ''; ?>
                    <?php echo ($d['forAddress2']) ? $d['forAddress2'] . ',<br/>' : ''; ?>
                    <?php echo ($d['forCity']) ? $d['forCity'] . ',<br/>' : '' ; ?>
                    <?php echo ($d['forPostcode']) ? '<strong>zipcode: </strong>'.$d['forPostcode'].',<br/>' : ''; ?>
                    <?php 
                          if($d['forAddress1'] && $d['forCity'] && $d['forPostcode']) { 
                                echo ($d['country']) ? $d['country'] : ''; 
                          } 
                     ?>

                    <?php } else {?>

                    <p style="text-decoration:underline;font-weight:bold">Local Address</p>

                    <?php echo $d['localAddress1'] . ',<br/>'; ?>
                    <?php echo ($d['localAddress2']) ? $d['localAddress2'] . ',<br/>' : ''; ?>
                    <?php // echo $d['city'] . ',<br/>'; ?>
                    <?php // echo $d['district'] . ','; ?>
                    <?php // echo $d['province'] . ','; ?>
                    <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>

                    <br/>
                    <br/>
                    <p style="text-decoration:underline;font-weight:bold">Foreign Address</p>
                    <?php echo $d['forAddress1'] . ',<br/>'; ?>
                    <?php echo ($d['forAddress2']) ? $d['forAddress2'] . ',<br/>' : ''; ?>
                    <?php echo $d['forCity'] . ',<br/>'; ?>
                    <?php echo ($d['forPostcode']) ? '<strong>zipcode: </strong>'.$d['forPostcode'].',<br/>' : ''; ?>
                    <?php echo ($d['country']) ? $d['country'] : ''; ?>

                    <?php }?>

                    </td>
                    <!-- <td width="5%" height="30" style="border-top: 0;"></td> -->
                    <!-- <td width="36.1%" height="60" style="border-top: 0;"> </td> -->
                </tr>
                </tbody>
            </table>

              <?php } ?>

       <br/>

                 <?php  
         
                $dc=0;
                if( count($shs) ){ 
                foreach($shs as $d ){ $dc++; ?>
            <table style="border:1; border-left:0;" width="100%" autosize="1">
                <tbody>
                <?php if ($dc == 1) {?>
                <tr style="border-bottom: 0;" >
                    <td width="100%" style="width: 509pt; border-bottom; border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all;" colspan="5">
                        <b>
                            <u style="font-size:15px">INITIAL MEMBERS</u>
                        </b><br/>
                        <span style="font-size: 14px;">
                            The following persons are the initial members of the
                        proposed
                        company:
                        </span>
                    </td>

                </tr>
                <?php } ?>

                <tr style="border-bottom: 0;" >
                    <td width="3.3%" rowspan="2" style="border-bottom: 0; "></td>
                    <td width="21.4%" rowspan="2"  class="bg-color">Full Name </td>
                    <td width="39.3%" rowspan="2" > <?php echo ( $d['firstname'].' '.$d['lastname'] );?></td>
                    <td width="36.1%" colspan="2" style="border-bottom: 0" class="bg-color">
                        <center>Email Address</center>
                    </td>
                </tr>
                <tr style="border-bottom: 0;">
                    <td colspan="2" height="30" style="text-align:center;"><?php echo $d['email'];?></td>
                </tr>

                <tr style=" border-bottom:0; ">
                    <td width="3.3%" height="40" style="border-bottom: 0; border-top:0"> <?php echo $dc;?> </td>
                    <td width="21.4%" height="40" class="bg-color">NIC No. /PP No. & <br>Country if
                        Foreigner</td>
                    <td width="39.3%" height="40" >
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
                    <!-- <td width="5%" style="border: 1; transform: rotate(270deg) translate(-5%, -20%); position: relative; vertical-align:middle; transform-origin:0 0;" rowspan="2"> Signature</td> -->
                    <td width="5%" height="40" class="a" rowspan="2" style="padding-left:10px; border-top:0" >
                    <img width="20" height="80" src="{{  URL::to('/') }}/images/signature.png" alt="signature"></td>
                    <td width="36.1%" height="40" rowspan="2" style="border-bottom: 1px;"></td>

                </tr>
                <tr style="border-top: 1;" >
                    <td width="3.3%" height="70" style="border-top: 0;"> </td>
                    <td width="21.4%" height="70" class="bg-color"> Residential Address</td>
                    <td width="39.3%" height="70" > 

                               <?php if ($d['type'] == 'local') {?>
                 
                        <?php echo $d['localAddress1'] . ',<br/>'; ?>
                        <?php echo ($d['localAddress2']) ? $d['localAddress2'] . ',<br/>' : ''; ?>
                        <?php // echo $d['city'] . ',<br/>'; ?>
                        <?php //echo $d['district'] . ','; ?>
                        <?php //echo $d['province'] . ','; ?>
                        <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>
                        <br/>
                        <br/>
                        <?php if($d['forAddress1'] && $d['forCity'] && $d['forPostcode']) { ?>
                            <p style="text-decoration:underline">Foreign Address</p>
                        <?php } ?>
                        
                        <?php echo ($d['forAddress1']) ? $d['forAddress1'] . ',<br/>' : ''; ?>
                        <?php echo ($d['forAddress2']) ? $d['forAddress2'] . ',<br/>' : ''; ?>
                        <?php echo ($d['forCity']) ? $d['forCity'] . ',<br/>' : '' ; ?>
                        <?php echo ($d['forPostcode']) ? '<strong>zipcode: </strong>'.$d['forPostcode'].',<br/>' : ''; ?>
                        <?php
                        if($d['forAddress1'] && $d['forCity'] && $d['forPostcode']) { 
                            echo ($d['country']) ? $d['country'] : ''; 
                        }
                        
                        ?>

                        <?php } else {?>

                        <p style="text-decoration:underline;font-weight:bold">Local Address</p>

                        <?php echo $d['localAddress1'] . ',<br/>'; ?>
                        <?php echo ($d['localAddress2']) ? $d['localAddress2'] . ',<br/>' : ''; ?>
                        <?php // echo $d['city'] . ',<br/>'; ?>
                        <?php // echo $d['district'] . ','; ?>
                        <?php // echo $d['province'] . ','; ?>
                        <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>

                        <br/>
                        <br/>
                        <p style="text-decoration:underline;font-weight:bold">Foreign Address</p>
                        <?php echo $d['forAddress1'] . ',<br/>'; ?>
                        <?php echo ($d['forAddress2']) ? $d['forAddress2'] . ',<br/>' : ''; ?>
                        <?php echo $d['forCity'] . ',<br/>'; ?>
                        <?php echo ($d['forPostcode']) ? '<strong>zipcode: </strong>'.$d['forPostcode'].',<br/>' : ''; ?>
                        <?php echo ($d['country']) ? $d['country'] : ''; ?>

                        <?php }?>

                    </td>
                    <!-- <td width="5%" height="30" style="border-top: 0;"></td> -->
                    <!-- <td width="36.1%" height="70" style="border-top: 0;"> </td> -->
                </tr>
                </tbody>
            </table>
                
                <?php } 
         
                }?>

                <?php  
                if( count($shFirms) ){ 
                foreach($shFirms as $d ){ $dc++; ?>
            <table style="border:1; border-left:0;" width="100%" autosize="1">
                <tbody>
                <?php if($dc == 1 && count($shs) == 0) { ?>
                <tr style="border-bottom: 0;" >
                    <td width="100%" style="width: 509pt; border-bottom; border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all;" colspan="5">
                        <b>
                            <u style="font-size:15px">INITIAL MEMBERS</u>
                        </b><br/>
                        <span style="font-size: 14px;">
                            The following persons are the initial members of the
                        proposed
                        company:
                        </span>
                    </td>

                </tr>
                <?php } ?>
                <tr style="border-bottom: 0;" >
                    <td width="3.3%" rowspan="2" style="border-bottom: 0; "> </td>
                    <td width="21.4%" rowspan="2"  class="bg-color">Full Name </td>
                    <td width="39.3%" rowspan="2" ><?php echo $d['title'];?></td>
                    <td width="36.1%" colspan="2" style=" border-bottom: 0" class="bg-color">
                        <center>Email Address</center>
                    </td>
                <tr style="border-bottom: 0;">
                    <td colspan="2" height="30"  style="text-align:center;"><?php echo $d['email'];?></td>
                </tr>

                <tr style="border-bottom: 0;">
                    <td width="3.3%" height="40" style="border-bottom: 0; border-top: 0;"> 2 </td>
                    <td width="21.4%" height="40" class="bg-color">Registration Number</td>
                    <td width="39.3%" height="40" ><?php echo $d['registration_no'];?> </td>
                    <!-- <td width="5%" style="border: 1; transform: rotate(270deg) translate(-5%, -20%); position: relative; vertical-align:middle; transform-origin:0 0;" rowspan="2"> Signature</td> -->
                    <td width="5%" height="40" class="a" rowspan="2" style="padding-left:10px;">
                    <img width="20" height="80" src="{{  URL::to('/') }}/images/signature.png" alt="signature"></td>
                    <td width="36.1%" height="40" rowspan="2" style="border-bottom: 1px;"></td>

                </tr>
                <tr style="border-top: 1px;" >
                    <td width="3.3%" height="70" style="border-top: 0;"> </td>
                    <td width="21.4%" height="70" class="bg-color"> Residential Address</td>
                    <td width="39.3%" height="70" > 
                              <?php  echo $d['localAddress1'].',<br/>'; ?>
                               <?php echo ($d['localAddress2']) ? $d['localAddress2'].',<br/>' : ''; ?>
                               <?php // echo $d['city'].',<br/>'; ?>
                               <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>

                    </td>
                    <!-- <td width="5%" height="30" style="border-top: 0;"></td> -->
                    <!-- <td width="36.1%" height="70" style="border-top: 0;"> </td> -->
                </tr>
                </tbody>
            </table>
                <!-- <tr >
                    <td width="3.3%" height="35" class="bg-color"  style="border: 0;">+</td>
                    <td width="21.4%" height="35" style="border: 0;"></td>
                    <td width="9.3%" height="35" style="border: 0;"> </td>
                    <td width="30%" height="35" class="bg-color" align="right" >Total Number of Shares</td>
                    <td width="5%" height="35" style="border-right:0;" ></td>
                    <td width="36.1%" height="35" style="border-left:0;"> </td>
                </tr> -->



                <?php } 
        
                }?>

       
        <?php
         $dc = 0;
        if(count($secs)) {
           
            foreach($secs as $d ){
            $dc++;
                ?>

        <table style="--primary-text-color: #212121;" width="100%" autosize="1">
            <tbody>
            <?php if ($dc == 1) {?>
            <tr style="border-bottom: 0;" >
                <td width="100%" style="width: 509pt; border-bottom; border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; text-align:justify" colspan="5">
                    <br>
                    <b>
                        <u style="font-size:15px">INITIAL SECRETARY/ SECRETARIES</u>
                    </b><br/>
                    <span style="font-size: 14px; ">The following persons(s) shall be the initial
                        secretary/secretaries* of the
                        proposed company and he/she/they* signify his/her/their* consent
                        by
                        signing below:
                        </span>
                </td>
            </tr>
            <?php } ?>
                <tr >
                    <td width="18%"height="50" class="bg-color">Full Name </td>
                    <td width="45%" height="50"><?php echo ( $d['firstname'].' '.$d['lastname'] );?></td>
                    <td rowspan="4" height="50" class="a"> <img width="20" height="80" src="{{  URL::to('/') }}/images/signature.png" alt="signature"></td>
                    <td rowspan="4" width="33.2%" height="50"></td>
                </tr>
                <tr >
                    <td width="18%" height="30" class="bg-color" >Registration No</td>
                    <td width="45%" height="30">
                       
                        <?php if ($d['isReg'] ) { echo $d['regDate']; } ?>
                    </td>
                </tr>
                <tr>
                    <td width="18%" height="30" class="bg-color" >Email address</td>
                    <td width="45%" height="30"><?php echo $d['email'];?></td>
                </tr>
                <tr >
                    <td width="18%" height="60" class="bg-color" >Permanent Address</td>
                    <td width="45%" height="60"><?php  echo $d['localAddress1'].',<br/>'; ?>
                                <?php echo ($d['localAddress2']) ? $d['localAddress2'].',<br/>' : ''; ?>
                                <?php  // echo $d['city'].',<br/>'; ?>
                                <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>
                </tr>
            </tbody>
        </table>
      

            <?php } } ?>

        

          <?php
        if(count($secFirms)) {

            foreach($secFirms as $d ){ 
                $dc++; ?>

        <table style="--primary-text-color: #212121;" width="100%">
            <tbody>
                <?php if ($dc == 1) {?>
                <tr style="border-bottom: 0;" >
                    <td width="100%" style="width: 509pt; border-bottom; border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; text-align:justify" colspan="5">
                        <br>
                        <b>
                            <u style="font-size:15px">INITIAL SECRETARY/ SECRETARIES</u>
                        </b><br/>
                        <span style="font-size: 14px; ">The following persons(s) shall be the initial
                            secretary/secretaries* of the
                            proposed company and he/she/they* signify his/her/their* consent
                            by
                            signing below:
                            </span>
                    </td>
                </tr>
                <?php } ?>
                <tr >
                    <td width="18%"height="50" class="bg-color">Full Name </td>
                    <td width="45%" height="50"><?php echo $d['title'];?></td>
                    <td rowspan="4" height="50" class="a"> <img width="20" height="80" src="{{  URL::to('/') }}/images/signature.png" alt="signature"></td>
                    <td rowspan="4" width="33.2%" height="50"></td>
                </tr>
                <tr >
                    <td width="18%" height="30" class="bg-color" >Registration No</td>
                    <td width="45%" height="30">
                        <?php echo $d['registration_no'];  ?></td>
                </tr>
                <tr>
                    <td width="18%" height="30" class="bg-color" >Email address</td>
                    <td width="45%" height="30"><?php echo $d['email'];?></td>
                </tr>
                <tr >
                    <td width="18%" height="60" class="bg-color" >Permanent Address</td>
                    <td width="45%" height="60"><?php  echo $d['localAddress1'].',<br/>'; ?>
                                <?php echo ($d['localAddress2']) ? $d['localAddress2'].',<br/>' : ''; ?>
                                <?php // echo $d['city'].',<br/>'; ?>
                                <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>
                </tr>
            </tbody>
        </table>
       

            <?php } } ?>

         <br/>

              <?php
        $shCounter = 0;
        foreach ($shs as $d) {
        $shCounter++;
        ?>
    <table width="100%" autosize="1">
        <tbody>
        <?php if ($shCounter == 1) {?>
        <tr>
            <td colspan="5" style="width:509pt; border-bottom; border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 14px;">
                <font style="font-size:15px"><b>
                        <b><u>SIGNATURE OF INITIAL MEMBERS:</u></b></font>
            </td>
        </tr>

        <?php }?>
        <tr>
            <td style="width:10pt;" height="80%"   ><?php echo $shCounter; ?></td>
            <td style="width:98pt;" height="80%"  class="bg-color" >Full Name</td>
            <td style="width:204pt;" height="80%"><?php echo ($d['firstname'] . ' ' . $d['lastname']); ?></td>
            <td style="width:28pt;" height="80%" class="a"><img width="20" height="80" src="{{  URL::to('/') }}/images/signature.png" alt="signature"></td>
            <td style="width:170pt;" height="80%"></td>
        </tr>
        </tbody>
    </table>
        <?php }?>

        <?php foreach ($shFirms as $d) {
        $shCounter++;
        ?>
    <table width="100%" autosize="1">
        <tbody>

        <?php if ($shCounter == 1) {?>
        <tr>
            <td colspan="5" style="width:509pt; border-bottom; border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 14px;">
                <font style="font-size:15px"><b>
                        <b><u>SIGNATURE OF INITIAL MEMBERS:</u></b></font>
            </td>
        </tr>
        <?php }?>
        <tr>
            <td style="width:10pt;" height="80%"   ><?php echo $shCounter; ?></td>
            <td style="width:98pt;" height="80%"  class="bg-color" >Full Name</td>
            <td style="width:204pt;" height="80%"><?php echo $d['title']; ?></td>
            <td style="width:28pt;" height="80%" class="a"><img width="20" height="80" src="{{  URL::to('/') }}/images/signature.png" alt="signature"></td>
            <td style="width:170pt;" height="80%"></td>
        </tr>
        </tbody>
    </table>
        <?php }?>
    <br>


        <table style="border: 0" width="100%" height="10">
            <tbody>
                <tr>
                <?php
                    $payment_time_stamp = ($payment_date) ? $payment_date : time();
                    $d = date('d', $payment_time_stamp);
                    $m = date('m', $payment_time_stamp);
                    $y = date('Y', $payment_time_stamp);

                    ?>
                    <td height="10" align="right" style="border: 0; ">Date:</td>
                    <td width="8%" style="border: 0"></td>
                    <td width="5%" style="text-align:center"><?php echo $d[0];?></td>
                    <td width="5%"  style="text-align:center"><?php echo $d[1];?></td>
                    <td width="10%" style="border: 0"></td>
                    <td width="5%" style="text-align:center"><?php echo $m[0];?></td>
                    <td width="5%" style="text-align:center"><?php echo $m[1];?></td>
                    <td width="10%" style="border: 0"></td>
                    <td width="5%" style="text-align:center"><?php echo $y[0];?></td>
                    <td width="5%" style="text-align:center"><?php echo $y[1];?></td>
                    <td width="5%" style="text-align:center"><?php echo $y[2];?></td>
                    <td width="5%" style="text-align:center"><?php echo $y[3];?></td>
                    <td width="4%" style="border: 0;"></td>
                </tr>
                <tr>
                    <td width="30%" height="10" style="border: 0"></td>
                    <td width="10%" style="border: 0"> </td>
                    <td colspan="2" class="bg-color">
                        <center>Day</center>
                    </td>
                    <td width="10%" style="border: 0"></td>
                    <td colspan="2" class="bg-color" >
                        <center>Month</center>
                    </td>
                    <td width="10%" style="border: 0"></td>
                    <td colspan="4" class="bg-color" >
                        <center>Year</center>
                    </td>
                </tr>
            </tbody>
        </table>
       <br/>

        <table width="100%" autosize="1">
            <tbody>
            <tr>
                <td colspan="2" style="width: 509pt; border-bottom; border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 13px;"> <strong><font ><b>Presented by:</b></font></strong></td>
            </tr>
                <tr height="20">
                    <td width="20%" class="bg-color" >Full Name </td>
                    <td width="80%"><?php echo $loginUser->first_name; ?></td>
                </tr>
                <tr height="20">
                    <td width="20%" class="bg-color" >Email Address</td>
                    <td width="80%"><?php echo $loginUser->email; ?></td>
                </tr>
                <tr height="20">
                    <td width="20%" class="bg-color" >Telephone No. </td>
                    <td width="80%"><?php echo $loginUser->telephone; ?></td>
                </tr>
                <tr height="20">
                    <td width="20%" class="bg-color" >Mobile No. </td>
                    <td width="80%"><?php echo $loginUser->mobile; ?></td>
                </tr>
                <tr >
                    <td width="20%" class="bg-color" height="50">Postal Address </td>
                    <td width="80%" height="50">

                     <?php  echo $loginUserAddress->address1.',<br/>'; ?>
                                 <?php  echo ( $loginUserAddress->address2 ) ? $loginUserAddress->address2.',<br/>' : ''; ?>
                                 <?php //  echo $loginUserAddress->city.',<br/>'; ?>
                                 <?php  echo '<strong>postcode: </strong>'.$loginUserAddress->postcode; ?>
                    </td>
                </tr>
            </tbody>
        </table>


           <htmlpagefooter name="page-footer" >
	<div class="page-no" style="text-align:right" >{PAGENO}</div>
        </htmlpagefooter>


</body>

</html>