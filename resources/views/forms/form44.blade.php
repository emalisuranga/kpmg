<html>
<head>
    <style>
           }
        @page {
	header: page-header;
	footer: page-footer;
        }
        table,th,td {
            border: #212121 solid 1px;
            border-collapse: collapse;
            margin-right: 0px;
            margin-left: 0px;
            margin-bottom: 0px;
            font-size: 14px;
            padding: 5px;
            padding-left:10px;
            font-family: sans-serif;
        }
        table{
                page-break-inside: avoid;
        }

        span {
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
      border-bottom: #000000;
      border-top: #000000;
      background: #dedcdc;
      position: relative;
  }
    
    body{
      /* margin-left: 20px; */
      font-family: sans-serif;

  }
    </style>
</head>
<body>
    <section class="form-body">
        <header class="form-header">
            <table width="100%" style="border:0; padding:0;">
                <tr>
                    <td width="10%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{ URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                    <td width="60%" style="border:0; font-size: 18px; padding-top:20px; padding-left:100px " align="center"><b>FORM 44<br><br></b></td>
                    <td width="12%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 489(d))</td>
                    <td width="18%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{ URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
                </tr>
                <tr>
                    <td colspan="4" align="center" style="border:0; font-size:16px; padding:0;"><b>FULL ADDRESS OF THE REGISTERED OR PRINCIPAL OFFICE OF A COMPANY INCORPORATED OUTSIDE SRI LANKA AND ITS PRINCIPAL PLACE OF BUSINESS ESTABLISHED IN SRI LANKA </b></td>
                </tr>
                <tr>
                    <td colspan="4"  style="border:0; padding:0px; font-size:13px; padding-left:230px;">The Companies Act No.7 of 2007 
Pursuant to Sec 489(d)
</td>
                </tr>
            </table>
            <br>

            <table style="border: 0;" width="100%" >
                <tbody>
                    <tr>
                        <td width="30%" height="35" style="border: 0;">Number of the Company </td>
                        <td width="7%" height="35">&nbsp;</td>
                        <td width="7%" height="35">&nbsp;</td>
                        <td width="56%" height="35">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
            <br>


            <table width="100%" height="30" autosize="1">
                <tbody>
                    <tr>
                        <td width="30%" height="50" class="bg-color">Name of the Company </td>
                        <td width="70%" height="50"><?php echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?></td>
                    </tr>
                    <tr height="20">
                        <td width="30%" class="bg-color">Registered or Principal Office Address (Country in which it is Incorporated)</td>
                        <td width="70%">
                            
                         <?php  echo $company_for_address->address1.',<br/>'; ?>
                        <?php echo ($company_for_address->address2) ? $company_for_address->address2.',<br/>' : ''; ?>
                        <?php  echo $company_for_address->city.',<br/>'; ?>
                        <?php  echo $company_for_address->province.',<br/>'; ?>
                         <?php  echo $company_for_address->country; ?>
                        </td>
                    </tr>
                    <tr height="20">
                        <td width="30%" class="bg-color">Principal place of Business<br> in Sri Lanka</td>
                        <td width="70%">

                         <?php  echo $company_address->address1.',<br/>'; ?>
                        <?php echo ($company_address->address2) ? $company_address->address2.',<br/>' : ''; ?>
                        <?php  // echo $company_address->city.',<br/>'; ?>
                        <?php  echo '<strong>postcode: </strong>'.$company_address->postcode; ?>
                        <?php // echo $company_address->postcode; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>


            <!-- <table width="100%" height="30">
                <tbody>
                    <tr >
                        <td width="30%" height="35" class="bg-color">Full Name </td>
                        <td width="70%" height="35">&nbsp;</td>
                    </tr>
                    <tr height="20">
                        <td width="30%" height="40" class="bg-color" style="padding-top:0; padding-bottom:0;">Passport No(indicate the Country of issue)</td>
                        <td width="70%" height="40" >&nbsp;</td>
                    </tr>
                    <tr height="20">
                        <td width="30%" height="40" class="bg-color">Resedential Address</td>
                        <td width="70%" height="40">&nbsp;</td>
                    </tr>
                    <tr height="20">
                        <td width="30%" height="40" class="bg-color">Other business or occupation/Directorships if any </td>
                        <td width="70%" height="40" >&nbsp;</td>
                    </tr>
                </tbody>
            </table>
            <br> -->


            <table width="100%">
                <tbody>
                   <?php   foreach($secs as $d ){ $dc++; ?>
                    <tr >
                        <td width="30%" height="80" class="bg-color">Full Name of Authorised Person</td>
                        <td width="70%" height="80"><?php echo ( $d['firstname'].' '.$d['lastname'] );?></td>
                    </tr>
                    <tr >
                        <td width="30%" height="50" class="bg-color">Signature of Authorised Person</td>
                        <td width="70%" height="50">&nbsp;</td>
                    </tr>
                  <?php } ?>
                </tbody>
            </table>
            <br>


            <table style="border: 0" width="100%" >
                <tbody>
                <tr>
                    <?php
                        $payment_time_stamp = ($payment_date) ? $payment_date : time();
                        $d = date('d', $payment_time_stamp);
                        $m = date('m', $payment_time_stamp);
                        $y = date('Y', $payment_time_stamp);

                    ?>
                    <td height="20"  style="border: 0"></td>
                    <td height="20" style="border: 0"></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $d[0];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $d[1];?></td>
                    <td height="20" style="border: 0"></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $m[0];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $m[1];?></td>
                    <td height="20" style="border: 0"></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $y[0];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $y[1];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $y[2];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $y[3];?></td>
                </tr>
                <tr>
                <td width="24%" height="5" style="border: 0" align="right">Date:</td>
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
            <br/>

            <table width="100%">
                <tbody>
                <tr  height="20">
                    <td colspan="2" style=" border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 14px;"> <strong><font ><b>Presented by:</b></font></strong></td>
                </tr>
                <tr height="20">
                                <td width="28%" >Full Name </td>
                                <td width="72%"><?php echo $loginUser->first_name; ?> <?php echo $loginUser->last_name; ?></td>
                        </tr>
                        <tr height="20">
                                <td width="28%">Email Address</td>
                                <td width="72%"><?php echo $loginUser->email; ?></td>
                        </tr> 
                        <tr  height="20">
                                <td width="28%">Telephone No.</td>
                                <td width="72%"><?php echo $loginUser->telephone; ?></td>
                        </tr> 
                <tr  height="20">
                                <td width="28%" >Mobile No.</td>
                                <td width="72%"><?php echo $loginUser->mobile; ?></td>
                        </tr > 
                        <tr  height="40">
                                <td width="28%" >Address</td>
                                <td width="72%">

                                <?php  echo $loginUserAddress->address1.',<br/>'; ?>
                                <?php  echo ( $loginUserAddress->address2 ) ? $loginUserAddress->address2.',<br/>' : ''; ?>
                                <?php  // echo $loginUserAddress->city.',<br/>'; ?>
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