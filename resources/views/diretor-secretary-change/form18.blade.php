<html>

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
      padding: 5px;
      font-family: sans-serif;
    }
    table{
                page-break-inside: avoid;
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
    <header class="form-header">
    <table width="100%" style="border:0; padding:0;">
            <tr>
                <td width="10%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{ URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                <td width="69%" style="border:0; font-size: 18px; padding-top:20px; padding-left:80px " align="center"><span><b>FORM 18<br><br></b></span><p style="font-size: 13px; ">Notice of</p><p style="font-size:16px;"><b>CONSENT AND CERTIFICATE OF DIRECTOR </b></p></td>
                <td width="11%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 203)</td>
                <td width="10%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{ URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>
            <!-- <tr>
                <td colspan="4" align="center" style="border:0; font-size:15px; padding:0;"><b>REGISTRATION OF A COMPANY</b> </td>
            </tr> -->
            <tr>
                <td colspan="4"  style="border:0; padding:0px; font-size:13px; padding-left:250px;">Section 203 of Companies Act No.
                    7 of 2007</td>
            </tr>
        </table>
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
      <table style="border: 0;" width="100%" >
          <tbody>
              <tr>
                  <td width="28%" style="border: 0; padding:0" >Number of the company </td>
                  <td width="72%" >&nbsp;<?php echo $certificate_no;?></td>
              </tr>
          </tbody>
      </table>
      <br>

      <table style="border: 0;" width="100%" >
          <tbody>
              <tr>
                  <td width="28%" height="50" class="bg-color">Name of the company </td>
                  <td width="72%" height="50">&nbsp; <?php echo $comname; ?>&nbsp;<?php echo $postfix; ?></td>
              </tr>
          </tbody>
      </table>
      <br>

      <table width="100%" >
        <tbody>
          <tr height="20">
            <td width="28%" class="bg-color" height="35" >First Name of Director </td>
            <td width="72%"><?php echo $fname;?></td>
          </tr>
          <tr height="20">
            <td width="28%" class="bg-color" height="35" >Last Name of Director</td>
            <td width="72%"><?php echo $lname;?></td>
          </tr>
          <tr height="20">
            <td width="28%" class="bg-color" height="25" >Occupation</td>
            <td width="72%"><?php echo $occupation;?></td>
          </tr>
          <tr height="20">
            <td width="28%" class="bg-color" height="20" style="padding-top:0; padding-bottom:0;">NIC No. (PP No. &<br> Country if a Foreigner) </td>
            <td width="72%">

              <?php 
                //print_r($directorDetails);
                                
                                if($nic){
                                    echo $nic;
                                }else if($passport){
                                    echo  $passport;  
                                    echo '<br/>';
                                    echo '<strong>Issued Country:</strong>'. $passport_issued_country;
                                }
                                
            ?>
            </td>
          </tr>
          <tr height="50">
            <td width="28%" class="bg-color" height="60" >Residential Address </td>
            <td width="72%">
            <?php echo $raddress;?>   
            </td>
          </tr>
        </tbody>
      </table>
      <br>

      <table style="border: 0" width="100%">
        <tbody>
          <tr>

            <?php 

              $o_date = $doa;

              $o_d = date('d', strtotime($o_date));
              $o_m = date('m', strtotime($o_date));
              $o_y = date('Y', strtotime($o_date));

              $o_d = ($o_date == '1970-01-01') ? '  ' : $o_d;
              $o_m = ($o_date == '1970-01-01') ? '  ' : $o_m;
              $o_y = ($o_date == '1970-01-01') ? '    ' : $o_y;

              ?>
            
            <td height="20"  style="border: 0"></td>
            <td height="20" style="border: 0"></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $o_d[0];?></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $o_d[1];?></td>
            <td height="20" style="border: 0"></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $o_m[0];?></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $o_m[1];?></td>
            <td height="20" style="border: 0"></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $o_y[0];?></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $o_y[1];?></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $o_y[2];?></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $o_y[3];?></td>
          </tr>
          <tr>
            <td width="24%" height="5" style="border: 0" align="right">Date of Appointment:</td>
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

      <!--<span style="text-align:justify">I, consent to be a director of the above company and certify that I am not disqualified from being appointed or holding
        office as a Director of a company.</span>-->

      <table width="100%" style="margin-top:2px;">
        <tbody>
        <tr>
            <td colspan="2" style="border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 14px;"> <strong><b>I, consent to be a director of the above company and certify that I am not disqualified from being appointed or holding
        office as a Director of a company.:</b></strong></td>
        </tr>
          <tr>
            <td height="90" width="28%" align="left" class="bg-color">Signature</td>
            <td height="90" width="72%" align="center">&nbsp;</td>
          </tr>
          <tr>
            <td height="50" width="28%" align="left" class="bg-color">Full Name of Director </td>
            <td height="50" width="72%"><?php echo $name;?></td>
          </tr>
        </tbody>
      </table>
      <br>

      <table style="border: 0" width="100%" >
        <tbody>
          <tr>
          <?php
            $payment_time_stamp = ($paymentdate) ? $paymentdate : time();
            $d = date('d', $payment_time_stamp);
            $m = date('m', $payment_time_stamp);
            $y = date('Y', $payment_time_stamp);

            ?>

            <td height="20"  style="border: 0"></td>
            <td height="20" style="border: 0"></td>
            <td height="20" width="4%" style="text-align:center;"><?php echo $d[0];?></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $d[1];?></td>
            <td height="20" style="border: 0"></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $m[0];?></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $m[1];?></td>
            <td height="20" style="border: 0"></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $y[0];?></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $y[1];?></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $y[2];?></td>
            <td height="20" width="4%" style="text-align:center;" ><?php echo $y[3];?></td>
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

      <table width="100%" autosize="1">
        <tbody>
        <tr>
            <td colspan="2" style="width: 509pt; border-bottom; border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 13px;"> <strong><font ><b>Presented by:</b></font></strong></td>
        </tr>
          <tr>
            <td width="28%" height="40" class="bg-color" >Full Name </td>
            <td width="72%" height="40"><?php echo $ufullname; ?></td>
          </tr>
          <tr>
            <td width="28%" height="20" class="bg-color" >Email Address</td>
            <td width="72%" height="20"><?php echo $uemail; ?></td>
          </tr>
          <tr>
            <td width="28%" height="20" class="bg-color" >Telephone No. </td>
            <td width="72%" height="20"><?php echo $utelephone; ?></td>
          </tr>
          <tr>
            <td width="28%" height="30" class="bg-color" >Mobile No. </td>
            <td width="72%" height="30"><?php echo $umobile; ?></td>
          </tr>
          <tr>
            <td width="28%" height="50" class="bg-color" style="padding-left:10px;">Address </td>
            <td width="72%" height="50" >
              <?php  echo $uraddress; ?>
            </td>
          </tr>
        </tbody>
      </table>

            
            <htmlpagefooter name="page-footer" >
	<div class="page-no" style="text-align:right" >{PAGENO}</div>
        </htmlpagefooter>

</body>

</html>