<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            header: page-header;
            footer: page-footer;
        }
        table,
        td {
            border: #212121 solid 1px;
            border-collapse: collapse;
            margin-right: 0px;
            margin-left: 0px;
            margin-bottom: 0px;
            font-size: 14px;
            padding: 5px;
            font-family: sans-serif;
        }
        table{
            page-break-inside: avoid;
            table-layout:fixed;
        }

        font {
            margin-left: 0px;
            margin-right: 0px;
            font-size: 14px;
            font-family: sans-serif;
            margin-bottom: 1px;
        }

        .bg-color {
            background: #b9b9b9;
        }

        .a {
            /* height: 5cm; */
            line-height: 12px;
            border-bottom: 1px solid #000000;
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
            <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 1<br><br></b></span><p style="font-size: 13px; ">Application for </p><p style="font-size:16px;"><b>REGISTRATION OF A COMPANY</b></p></td>
            <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 4(1))</td>
            <td width="20%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
        </tr>
        <!-- <tr>
            <td colspan="4" align="center" style="border:0; font-size:15px; padding:0;"><b>REGISTRATION OF A COMPANY</b> </td>
        </tr> -->
        <tr>
            <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">Section 4(1) of Companies Act No.
                7 of 2007 (“the Act”)</td>
        </tr>
    </table>
    <br>

    <table style="border: 0;" width="100%" >
        <tbody>
        <tr >
            <td width="23%" height="40" style="border: 0;"> Company Number</td>
            <td width="7%"  height="40" >&nbsp;</td>
            <td width="7%"  height="40" >&nbsp;</td>
            <td width="63%" height="40" >&nbsp;</td>
        </tr>
        </tbody>
    </table>
    <br>
    <table style="border: 0;" width="100%" >
        <tbody>
        <tr>
            <td width="23%" height="30" style="border: 0;">Type of the Company </td>
            <td width="77%" height="30"><?php echo $company_type; ?></td>
        </tr>
        </tbody>
    </table>
    <br>
    <table style="--primary-text-color: #212121; " width="100%">
        <tbody>
        <tr>
            <td width="26%"  class="bg-color" >Name <br>Approval Number </td>
            <td width="74%"><?php echo $company_info->id; ?></td>
        </tr>
        <tr>
            <td width="26%" class="bg-color" ><br>Name of Proposed<br> Company</td>
            <td width="74%"><?php echo ($company_info->name) ? $company_info->name . ' ' . $postfix : '' ?></td>
        </tr>
        <tr>
            <td width="26%"  class="bg-color" >Registered Address</br>
                <span style="font-size: 12px;"><I>(Physical Address in Sri Lanka and must not be a PO Box
                            Address)<br><br></I></span>
            </td>
            <td width="74%" height="80">

                <?php
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
            <td style="border: 0; color:#000000; padding:0">
                <b><u>DECLARATION UNDER SECTION 4(1)(a) OF THE ACT</u></b>
            </td>
        </tr>
        <tr>
            <td style="border: 0; text-align:justify; padding:0; padding-bottom:10px;">I/We declare that to the best
                of my/our knowledge the name of this proposed
                company is not identical or similar to that of any existing
                company.</td>
        </tr>
        </tbody>
    </table>

    <table style="border:0" width="100%">
        <tbody>
        <tr>
            <td style=" border:0; color:  #212121;" colspan="2">
                <b>
                    <u>ARTICLES OF ASSOCIATION</u>
                </b>
            </td>
        </tr>
        <tr>
            <td width="7%" style="border: 0; "></td>
            <td width="93%" style=" border:0; padding: 0; text-align:justify; ">
                <ul>
                    <li>The Articles of Association of the proposed company
                        shall
                        be as set out in the first schedule to the Act.</li>
                    <li>The Articles of Association of the proposed company
                        shall
                        be as set out in the Annexure "A" signed by
                        each
                        of us.</li>
                </ul>
            </td>
        </tr>
        </tbody>
    </table>
 

   

    <hr/>

    <?php
    $dc = 0;

    foreach ($directors as $d) {$dc++;?>


    <table style="border: 1; padding:0" width="100%" autosize="1">
        <tbody>

        <?php if ($dc == 1) {?>
        <tr>
            <td colspan="5" style="width: 509pt; border-bottom; border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 14px; text-align:justify ">
                <font style="font-size:14px"><b>
                        <u>INITIAL DIRECTORS</u>
                    </b></font>
                <Br/>
                <span style=" word-break: break-all;">The following persons are the initial directors of the proposed company and signify their consent by signing below and  certify that each one of them is not disqualified from being appointed or holding office<br/> as a director of a company:</span>

            </td>
        </tr>
        <?php }?>

        <tr style="border-bottom: 0;" >
            <td height="0.5cm" rowspan="2" style="border-bottom: 0; width: 18pt;">&nbsp;</td>
            <td height="0.5cm" rowspan="2" class="bg-color" style="width: 114pt;">Full Name </td>
            <td height="0.5cm" rowspan="2" style="width: 200pt;">
                <?php echo ($d['firstname'] . ' ' . $d['lastname']); ?>
            </td>
            <td height="0.5cm" colspan="2" style="width: 177pt; border-bottom: 0; overflow:wrap;" class="bg-color">
                <center>Email Address</center>
            </td>
        </tr>
        <tr style="border-bottom: 0;">
            <td colspan="2" height="30" style="width:177pt; text-align:center;"><?php echo $d['email']; ?></td>
        </tr>
        <tr style="border: 0;" >
            <td height="40" style="border-bottom: 0; border-top: 0; width:18pt;"> <?php echo $dc; ?> </td>
            <td height="40"  class="bg-color" style="width: 114pt;">NIC No.</td>
            <td height="40" style="width: 200pt;">
                <?php echo $d['nic']; ?>
            </td>
            <td height="40"  class="a" rowspan="3"  style="padding-left:10px; width:32pt;" >
                <img width="16px" height="80" src="{{  URL::to('/') }}/images/signature.png" alt="signature"></td>
            <td height="40" rowspan="2" style="border-bottom: 0; width:145pt;">&nbsp;</td>

        </tr>
        <tr style="border-top: 0;" >
            <td style="border-top: 0; border-bottom:0; width: 18pt;">&nbsp;</td>
            <td class="bg-color" style="width:114pt;">Passport No. & Country</td>
            <td style="width:200pt;">
                <?php
                if ($d['country'] != 'Sri Lanka') {
                    echo 'Passport No: ' . $d['passport'];
                    echo '<br/>';
                    echo 'Country: ' .$d['passport_issued_country'];;
                }
                ?>
            </td>
            <!-- <td width="5%" height="30" style="border-top: 0;"></td> -->
            <!-- <td width="36.1%" height="60" style="border-top: 0;"> </td> -->
        </tr>
        <tr style="border-top: 0;" >
            <td height="60" style="border-top: 0; width: 18pt;"> </td>
            <td height="60" class="bg-color" style="width:114pt;"> Residential Address</td>
            <td height="60" style="width:200pt;">

            <?php //print_r($d); ?>

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
    <?php }?>

    <br>

    <table width="100%" autosize="1">
        <tbody>
        <tr>
            <td colspan="2" style="width: 509pt; border-bottom; border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 13px;"> <strong><font ><b>Presented by:</b></font></strong></td>
        </tr>
        <tr>
            <td style="width:132pt;" height="50" class="bg-color">Full Name </td>
            <td style="width:380pt;" height="50"><?php echo $loginUser->first_name; ?> <?php echo $loginUser->last_name; ?></td>
        </tr>
        <tr height="10px">
            <td style="width:132pt;" class="bg-color">Email Address</td>
            <td style="width:380pt;"><?php echo $loginUser->email; ?></td>
        </tr>
        <tr height="10px">
            <td style="width:132pt;" height="35" class="bg-color">Telephone No.</td>
            <td style="width:380pt;" height="35"><?php echo $loginUser->telephone; ?></td>
        </tr>
        <tr height="10px">
            <td style="width:132pt;" height="30" class="bg-color">Mobile No.</td>
            <td style="width:380pt;" height="30"><?php echo $loginUser->mobile; ?></td>
        </tr>
        </tbody>
    </table>
    <br> 

    <?php
    $count_sh = count($shs);
    $count_shFirms = count($shFirms);
    $sh = 0;
    $sharecount = 0;
    ?>

    <?php if ($count_sh) {
    $core_share_steps = 0;
    foreach ($shs as $key => $d) {

        $sh++;
        
        ?>
    <table width="100%" border="1" autosize="1">
        <tbody>
        <?php if($sh == 1) { ?>
        <tr>
            <td colspan="7" style="text-align: left; border:1px solid #fff; border-bottom:1px solid #000; word-break: break-all;">
                <h4 style="font-size:15px; text-align: left;"><u>INITIAL SHAREHOLDERS</u></h4>
                <span style="font-weight: 400; font-size: 14px; word-break: break-all; text-align:justify"> The following persons are the initial
                    shareholders of the proposed company:</span>
                <br>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <td rowspan="2" style="border-bottom:0; width:20pt;"> </td>
            <td rowspan="2" class="bg-color" style="width:102pt;">Full Name<br>/Company Name </td>
            <td colspan="2" rowspan="2" style="width:194pt;"> <?php echo ($d['firstname'] . ' ' . $d['lastname']); ?></td>
            <td class="bg-color" colspan="2" style="width:93pt;">Number of<br> Shares</td>
            <td style="width:100pt;">

                <?php if ($d['share']['value']) {

                    $shareType = $d['share']['type'] == 'single share' ? '' : $d['share']['type'];
                    if ($shareType == 'core share') {
                        $shareType = '(joint share)';
                    }

                    echo $d['share']['value'] . $shareType;

                    if ($d['share']['type'] == 'core share' && $core_share_steps == 0) {
                        $sharecount += $d['share']['value'];
                        $core_share_steps++;
                    }
                    if ($d['share']['type'] != 'core share') {
                        $sharecount += $d['share']['value'];
                    }
                }?>
            </td>

        </tr>
        <tr class="bg-color" >
            <td colspan="3" height="30" style="width:193pt;"></td>
        </tr>
        <tr height="4cm">
            <td style="width:20pt; border-bottom:0; border-top:0;"><?php echo $sh; ?></td>
            <td height="50" class="bg-color" style="width:102pt;">NIC No./Passport & Country<span style="font-size:10px;"> (if a
                            Foreigner)</span>/ Company No.<span style="font-size:10px;"> (if a Company)</span>
            </td>
            <td colspan="2" style="width:194pt;">
                <?php
                if ($d['country'] != 'Sri Lanka') {
                    echo 'Passport No: ' . $d['passport'];
                    echo '<br/>';
                    echo 'Country: ' .$d['passport_issued_country'];;
                } else {
                    echo $d['nic'];
                }
                ?>
            </td>
            <td rowspan="2" class="a" style="width:30pt;">  <img width="20" height="80" src="{{  URL::to('/') }}/images/signature.png" alt="signature"></td>
            <td rowspan="2" style="border:0;border-bottom:1px solid #000; width:72pt;" ></td>
            <td style="border:0; width:101pt;"></td>
        </tr>
        <tr >
            <td style="border-top:0; width:20pt;"> </td>
            <td style="width:102pt;" height="70" class="bg-color" > Residential  Address</td>
            <td style="width:194pt;" colspan="2" height="70" >

                <?php
                      //  print_r($d);
 
                        if($d['type'] == 'local'){ ?>

                                <p style="text-decoration:underline;font-weight:bold">Local Address</p>

                                <?php  echo $d['localAddress1'].',<br/>'; ?>
                                <?php echo ($d['localAddress2']) ? $d['localAddress2'].',<br/>' : ''; ?>
                                <?php // echo $d['city'].',<br/>'; ?>
                                <?php  // echo $d['district'].','; ?>
                                <?php  // echo $d['province'].','; ?>
                                <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>

                                 <?php 
                                    if( $d['forProvince'] &&  $d['forCity'] && $d['forAddress1'] && $d['forPostcode'] && $d['country'] ) { ?>

                                         <p style="text-decoration:underline;font-weight:bold"><br/>Foreign Address</p>

                                         <?php  echo $d['forAddress1'].',<br/>'; ?>
                                         <?php echo ($d['forAddress2']) ? $d['forAddress2'].',<br/>' : ''; ?>
                                         <?php  echo $d['forCity'].',<br/>'; ?>
                                         <?php  echo $d['forProvince'].',<br/>'; ?>
                                         <?php echo '<strong>zipcode: </strong>'.$d['forPostcode'].',<br/>'; ?>
                                         <?php  echo $d['country'].',<br/>'; ?>
                                    <?php

                                    }

                                 ?>

                                <?php }else { ?>

                                <p style="text-decoration:underline;font-weight:bold">Foreign Address</p>

                                    <?php  echo $d['forAddress1'].',<br/>'; ?>
                                    <?php echo ($d['forAddress2']) ? $d['forAddress2'].',<br/>' : ''; ?>
                                    <?php  echo $d['forCity'].',<br/>'; ?>
                                    <?php  echo $d['forProvince'].',<br/>'; ?>
                                    <?php echo '<strong>zipcode: </strong>'.$d['forPostcode'].',<br/>'; ?>
                                    <?php  echo $d['country'].',<br/>'; ?>


                                 <?php
                                 if(  $d['localAddress1'] && $d['localAddress2'] && $d['postcode']) { ?>

                                    <p style="text-decoration:underline;font-weight:bold"><br/>Local Address</p>

                                    <?php  echo $d['localAddress1'].',<br/>'; ?>
                                    <?php echo ($d['localAddress2']) ? $d['localAddress2'].',<br/>' : ''; ?>
                                    <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>


                                  <?php } ?>



                               

                                <?php } ?>
            </td>
            <td style="border:0;border-bottom:1px solid #000; width:101pt;" height="70" > </td>
        </tr>
        </tbody>
    </table>
    <?php 

    }}?>

    <?php
    if ($count_shFirms) {
    $core_share_steps = 0;

    foreach ($shFirms as $key => $d) {
        $sh++;
    ?>
    <table width="100%" autosize="1">
        <tbody>

        <?php if($sh == 1 && $count_sh == 0) { ?>
        <tr>
            <td colspan="7" style="text-align: left; border:1px solid #fff; border-bottom:1px solid #000; word-break: break-all;">
                <h4 style="font-size:15px; text-align: left;"><u>INITIAL SHAREHOLDERS</u></h4>
                <span style="font-weight: 400; font-size: 14px; word-break: break-all; text-align:justify"> The following persons are the initial
                    shareholders of the proposed company:</span>
                <br>
            </td>
        </tr>
        <?php } ?>

        <tr>
            <td rowspan="2" style="border-bottom:0; width: 20pt;"> </td>
            <td rowspan="2" class="bg-color" style="width:102pt;">Full Name<br>/Company Name </td>
            <td colspan="2" rowspan="2" style="width:194pt;"> <?php echo $d['title']; ?></td>
            <td class="bg-color" colspan="2" style="width:92pt;">Number of<br> Shares</td>
            <td style="width:101pt;">
                <?php if ($d['share']['value']) {
                    $shareType = $d['share']['type'] == 'single share' ? '' : $d['share']['type'];
                    if ($shareType == 'core share') {
                        $shareType = '(joint share)';
                    }

                    echo $d['share']['value'] . $shareType;

                    if ($d['share']['type'] == 'core share' && $core_share_steps == 0) {
                        $sharecount += $d['share']['value'];
                        $core_share_steps++;
                    }
                    if ($d['share']['type'] != 'core share') {
                        $sharecount += $d['share']['value'];
                    }
                }?>
            </td>
        <tr class="bg-color" >
            <td colspan="3" height="30" style="width:193pt;"></td>
        </tr>
        <tr height="4cm">
            <td style="border-bottom:0; border-top:0; width:20pt;"><?php echo $sh; ?></td>
            <td height="50" class="bg-color" style="width:102pt;">NIC No./Passport & Country<span style="font-size:10px;"> (if a
                            Foreigner)</span>/ Company No<span style="font-size:10px;"> (if a Company)</span>
            </td>
            <td style="width:194pt;" colspan="2"><?php echo $d['registration_no'] ?></td>
            <td style="width:30pt;" rowspan="2" class="a">  <img width="20" height="80" src="{{  URL::to('/') }}/images/signature.png" alt="signature"></td>
            <td rowspan="2" style="border:0; width:72pt;" ></td>
            <td style="border:0; width:101pt;"></td>
        </tr>
        <tr >
            <td style="border-top:0; width:20pt;"> </td>
            <td style="width:102pt;" height="70" class="bg-color" > Residential  Address</td>
            <td style="width:194pt;" colspan="2" height="70" >
                <?php echo $d['localAddress1'] . ',<br/>'; ?>
                <?php echo ($d['localAddress2']) ? $d['localAddress2'] . ',<br/>' : ''; ?>
                <?php // echo $d['city'] . ',<br/>'; ?>
                <?php // echo ($d['district']) ? $d['district'] . ',' : ''; ?>
                <?php // echo $d['province'] . ','; ?>
                <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>
            </td>
            <td width="20%" style="border:0;border-bottom:1px solid #000; width:101pt;" height="70" ></td>
        </tr>
        </tbody>
    </table>
    <?php }}?>
    <table>
        <tbody>
        <tr >
            <td style="border:#fff solid 1px;border-right:#000 solid 1px; width:20pt;"></td>
            <td style="width:296pt; border-top: #fff;" class="bg-color" align="left" height="35" >Total Number of Shares</td>
            <td style="width:203pt; border-top: #fff;"><?php echo  $total_shares; ?> </td>
        </tr>
        </tbody>
    </table>
    <br>

    <?php
    $count_sec = count($secs);
    $count_secFirms = count($secFirms);
    $secCount = 0;
    foreach ($secs as $d) {
    $secCount++;
    ?>

    <table style="--primary-text-color: #212121; " width="100%" autosize="1">
        <tbody>
        <?php if ($secCount == 1) {?>

        <tr>
            <td colspan="4" style="width:509pt; border-bottom; border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 14px;">
                <font style="font-size:15px"><b>
                        <b><u>INITIAL SECRETARY/ SECRETARIES</u></b>
                    </b></font>
                <Br/>
                The following persons(s) shall be the initial
                secretary/secretaries* of the
                proposed company and he/she<br/>/they signify his/her/their consent
                by
                signing below:

            </td>
        </tr>

        <?php }?>

        <tr >
            <td style="width:82pt;" height="40" class="bg-color">Full Name </td>
            <td style="width:229pt;" height="40"><?php echo ($d['firstname'] . ' ' . $d['lastname']); ?></td>
            <td style="width:28pt;"  height="40" rowspan="4" class="a"><img width="20" height="80" src="{{  URL::to('/') }}/images/signature.png" alt="signature"></td>
            <td style="width:170pt;" height="40" rowspan="4" ></td>
        </tr>
        <tr >
            <td style="width:82pt;" class="bg-color" >Registration <br> No.</td>
            <td style="width:229pt;"> <?php if ($d['isReg']) {echo $d['regDate'];}?></td>
        </tr>
        <tr >
            <td style="width:82pt;" class="bg-color" >Email Address</td>
            <td style="width:229pt;"><?php echo $d['email']; ?></td>
        </tr>
        <tr >
            <td style="width:82pt;" height="60" class="bg-color">Permanent Address</td>
            <td style="width:229pt;" height="60">
                <?php echo $d['localAddress1'] . ',<br/>'; ?>
                <?php echo ($d['localAddress2']) ? $d['localAddress2'] . ',<br/>' : ''; ?>
                <?php // echo $d['city'] . ',<br/>'; ?>
                <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>
            </td>
        </tr>
        </tbody>
    </table>
    <?php }?>

    <?php foreach ($secFirms as $d) {
    $secCount++;
    ?>

    <table style="--primary-text-color: #212121; " width="100%" autosize="1">
        <tbody>

        <?php if ($secCount == 1) {?>

        <tr>

            <td colspan="4" style="width:509pt; border-bottom; border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 14px;">
                <font style="font-size:15px"><b>
                        <b><u>INITIAL SECRETARY/ SECRETARIES</u></b>
                    </b></font>
                <Br/>
                The following persons(s) shall be the initial
                secretary/secretaries* of the
                proposed company and he/she<br/>/they signify his/her/their consent
                by
                signing below:

            </td>
        </tr>


        <?php }?>

        <tr  >
            <td style="width:82pt;" height="40" class="bg-color">Full Name </td>
            <td style="width:229pt;" height="40"><?php echo $d['title']; ?></td>
            <td style="width:28pt;"  height="40" rowspan="4" class="a"><img width="20" height="80" src="{{  URL::to('/') }}/images/signature.png" alt="signature"></td>
            <td style="width:170pt;" height="40" rowspan="4" ></td>
        </tr>
        <tr >
            <td style="width:82pt;" class="bg-color" >Registration <br> No.</td>
            <td style="width:229pt;">  <?php echo $d['registration_no']; ?></td>
        </tr>
        <tr >
            <td style="width:82pt;" class="bg-color" >Email Address</td>
            <td style="width:229pt;"><?php echo $d['email']; ?></td>
        </tr>
        <tr >
            <td style="width:82pt;" height="60" class="bg-color">Permanent Address</td>
            <td style="width:229pt;" height="60">
                <?php echo $d['localAddress1'] . ',<br/>'; ?>
                <?php echo ($d['localAddress2']) ? $d['localAddress2'] . ',<br/>' : ''; ?>
                <?php // echo $d['city'] . ',<br/>'; ?>
                <?php echo '<strong>postcode: </strong>'.$d['postcode']; ?>
            </td>
        </tr>
        </tbody>
    </table>
    <?php }?>
    <br>


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
                        <b><u>SIGNATURE OF INITIAL SHAREHOLDERS:</u></b></font>
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
                        <b><u>SIGNATURE OF INITIAL SHAREHOLDERS:</u></b></font>
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

    <table  width="100%" height="50.4" style="border:0">
        <tbody>
        <tr>
            <?php
            $payment_time_stamp = ($payment_date) ? $payment_date : time();
            $d = date('d', $payment_time_stamp);
            $m = date('m', $payment_time_stamp);
            $y = date('Y', $payment_time_stamp);

            ?>
           <td width="4%" style="border:0"    ></td> 
            <td height="30" align="right" style="border:0">Date:</td>
            <td width="8%" style="border:0"    ></td>
            <td width="5%" style="text-align:center"><?php echo $d[0]; ?></td>
            <td width="5%" style="text-align:center"><?php echo $d[1]; ?></td>
            <td width="10%"  style="border:0"   ></td>
            <td width="5%" style="text-align:center"><?php echo $m[0]; ?></td>
            <td width="5%" style="text-align:center"><?php echo $m[1]; ?></td>
            <td width="10%" style="border:0"    ></td>
            <td width="5%" style="text-align:center"><?php echo $y[0]; ?></td>
            <td width="5%" style="text-align:center"><?php echo $y[1]; ?></td>
            <td width="5%" style="text-align:center"><?php echo $y[2]; ?></td>
            <td width="5%" style="text-align:center"><?php echo $y[3]; ?></td>
            <!-- <td width="4%" style="border:0"    ></td> -->
        </tr>
        <tr>
        <td width="4%" style="border:0"    ></td>
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

    <htmlpagefooter name="page-footer" >
        <div class="page-no" style="text-align:right" >{PAGENO}</div>
    </htmlpagefooter>

</body>
</html>