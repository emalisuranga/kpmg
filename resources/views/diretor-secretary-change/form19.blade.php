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
            ;
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

        /* .a> h6 {
            float: left;
            transform: rotate(-90deg);
            -webkit-transform: rotate(-90deg);  
            -ms-transform: rotate(-90deg); 
       

        } */

        body {
            /* margin-left: 20px; */
            font-family: sans-serif;

        }
        </style>

</head>

<body>
  <section class="form-body">
    <header class="form-header">
  
        <table width="100%" style="border:0; padding:0;">
            <!-- <tr>
                <td width="10%" style="border:0; padding:0px; "><img width="100px" height="100px" src="{{asset('/form-images/govlogo.jpg')}}" alt="gov_logo"></td>
                <td width="69%" style="border:0; font-size: 20px; padding-top:20px; padding-left:22px;  " align="center"><span><b>FORM 19<br><br></b></span><p style="font-size: 13px; ">Notice of</p><p style="font-size:15px;"><b>CONSENT AND CERTIFICATE OF SECRETARY/ SECRETARIES </b></p></td>
                <td width="11%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 221(2))</td>
                <td width="10%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{asset('/form-images/eroc.png')}}" alt="Logo EROC"></td>
            </tr> -->
            <!-- <tr>
                <td colspan="4" align="center" style="border:0; font-size:15px; padding:0;"><b>REGISTRATION OF A COMPANY</b> </td>
            </tr> -->
            <!-- <tr>
                <td colspan="4"  style="border:0; padding:0px; font-size:13px; padding-left:180px;">Section 4(1) of Companies Act No.
                    7 of 2007 (“the Act”)</td>
            </tr> -->
            <tr>
              <td style="border:0;"><img width="100px" height="100px" src="{{ URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
              <td style="border:0;">
                  <table style="padding:0; border:0;">
                      <tr style="padding:0; border:0;">
                          <td  style="padding-left:170px; font-size:18px; border:0; padding-bottom:20px;" height="50px;"><b>FORM 19</b></td>
                          <td  style="padding:0; border:0; font-size:12px;" align="right"  height="50px;">(Section 221(2))</td>
                      </tr>
                      <tr>
                          <td  style="padding:0; font-size:12px; border:0; padding-bottom:5px;" colspan="2" align="center"  height="50px;">Notice of</td>
                      </tr>
                      <tr>
                          <td  style="padding:0; font-size:16px; border:0; padding-bottom:3px;" colspan="2" align="center"  height="50px;"><b>CONSENT AND CERTIFICATE OF SECRETARY/ SECRETARIES</b></td>
                      </tr>
                      <tr>
                          <td  style="padding:0; font-size:14px; border:0; font-size:11px;" colspan="2" align="center"  height="50px;">Section 221(2) of Companies Act No.
                    7 of 2007</td>
                      </tr>
                  </table>
              </td>
              <td align="right" style="border:0;"><img width="130" height="auto" src="{{ URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
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

      <table  width="100%" >
        <tbody>
          <tr >
            <td width="28%" height="35" class="bg-color" >First Name of Secretary </td>
            <td width="72%" height="35"><?php echo $fname;?></td>
          </tr>
          <tr >
            <td width="28%" height="35" class="bg-color">Last Name of Secretary</td>
            <td width="72%" height="35"><?php echo $lname;?></td>
          </tr>
          <tr>
            <td width="28%" height="60" class="bg-color" >Residential Address </td>
            <td width="72%" height="60" >
            <?php echo $raddress;?>
            </td>
          </tr>
        </tbody>
      </table>
      <br>

      <font>I/We consent to be Secretary/Secretaries of the above company and certify that I am qualified to be appointed to hold office of Secretary.</font>
      <br>
     
   
      <table style="border: 0; margin-top:6px;" width="100%">
        <tbody>
          <tr>

             <?php 

              $o_date = $doa;

             

              $o_d = date('d', strtotime($o_date));
              $o_m = date('m', strtotime($o_date));
              $o_y = date('Y', strtotime($o_date));

              $o_d = ($o_date == '1970-01-01' || $o_date == '' ) ? '  ' : $o_d;
              $o_m = ($o_date == '1970-01-01' || $o_date  == '' ) ? '  ' : $o_m;
              $o_y = ($o_date == '1970-01-01' || $o_date  == '' ) ? '    ' : $o_y;

              ?>

             <td height="22" align="right" style="border: 0"></td>
            <td style="border: 0"></td>
            <td width="6%" align="center"><?php echo $o_d[0];?></td>
            <td width="6%" align="center"><?php echo $o_d[1];?></td>
            <td style="border: 0"></td>
            <td width="6%" align="center"><?php echo $o_m[0];?></td>
            <td width="6%" align="center"><?php echo $o_m[1];?></td>
            <td style="border: 0"></td>
            <td width="6%" align="center"><?php echo $o_y[0];?></td>
            <td width="6%" align="center"><?php echo $o_y[1];?></td>
            <td width="6%" align="center"><?php echo $o_y[2];?></td>
            <td width="6%" align="center"><?php echo $o_y[3];?></td>
          </tr>
          <tr>
            <td width="24%" height="22" style="border: 0">Date of Appointment:</td>
            <td width="6%" style="border: 0"> </td>
            <td colspan="2" class="bg-color">
              <center>Day</center>
            </td>
            <td width="6%" style="border: 0"></td>
            <td colspan="2" class="bg-color">
              <center>Month</center>
            </td>
            <td width="6%" style="border: 0"></td>
            <td colspan="4" class="bg-color">
              <center>Year</center>
            </td>
          </tr>
        </tbody>
      </table>
      <br>

      <table  width="100%" height="20">
        <tbody>
          <tr>
            <td width="28%" class="bg-color">Registration Number<br><span style="font-size:12px;">(if Applicable)</span> </td>
            <td width="72%"> <?php echo $regnum;?></td>
          </tr>
        </tbody>
      </table>
      <br>

      <table  width="100%">
        <tbody>
          <tr >
            <td width="28%" height="90" class="bg-color" align="left">Signature</td>
            <td width="72%" height="90" >&nbsp;</td>
          </tr>
          <tr>
            <td width="28%" height="50" class="bg-color" align="left">Full Name of Secretary </td>
            <td width="72%"  height="50"><?php echo $name;?></td>
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
              <td height="22" align="right" style="border: 0">Date:</td>
            <td style="border: 0"></td>
            <td  width="6%" align="center"><?php echo $d[0];?></td>
            <td  width="6%" align="center"><?php echo $d[1];?></td>
            <td style="border: 0"></td>
            <td  width="6%" align="center"><?php echo $m[0];?></td>
            <td  width="6%" align="center"><?php echo $m[1];?></td>
            <td style="border: 0"></td>
            <td  width="6%" align="center"><?php echo $y[0];?></td>
            <td  width="6%" align="center"><?php echo $y[1];?></td>
            <td  width="6%" align="center"><?php echo $y[2];?></td>
            <td  width="6%" align="center"><?php echo $y[3];?></td>
            </tr>
            <tr>
              <td width="24%" height="22" style="border: 0"></td>
              <td width="6%" style="border: 0"> </td>
              <td colspan="2" class="bg-color">
                <center>Day</center>
              </td>
              <td width="6%" style="border: 0"></td>
              <td colspan="2" class="bg-color">
                <center>Month</center>
              </td>
              <td width="6%" style="border: 0"></td>
              <td colspan="4" class="bg-color">
                <center>Year</center>
              </td>
            </tr>
  
          </tbody>
        </table>

    <!--  <strong><font>Presented by:</font></strong> -->

      <table  width="100%">
        <tbody>
        <tr>
            <td colspan="2" style="border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 14px;"> <strong><b>Presented by:</b></strong></td>
        </tr>
          <tr>
            <td width="28%"  height="40" class="bg-color">Full Name </td>
            <td width="72%"  height="40"><?php echo $ufullname; ?></td>
          </tr>
          <tr  >
            <td width="28%" class="bg-color">Email Address</td>
            <td width="72%"><?php echo $uemail; ?></td>
          </tr>
          <tr >
            <td width="28%" class="bg-color">Telephone No. </td>
            <td width="72%"><?php echo $utelephone; ?></td>
          </tr>
          <tr >
            <td width="28%" class="bg-color">Mobile No. </td>
            <td width="72%" ><?php echo $umobile; ?></td>
          </tr>
          <tr >
            <td width="28%" height="60" class="bg-color">Address </td>
            <td width="72%" height="60" >
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