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


      <table style="border: 0;" width="100%" >
        <tbody>
          <tr>
            <td width="20%" style="border: 0; padding:0" >Number of the Company </td>
            <td  width="7%" >&nbsp;</td>
            <td  width="7%" >&nbsp;</td>
            <td  width="66%" >&nbsp;</td>
          </tr>
        </tbody>
      </table>
      <br>


      <table style="border: 0;" width="100%" >
        <tbody>
          <tr>
            <td width="28%" height="50" class="bg-color">Name of the Company </td>
            <td width="72%" height="50"><?php echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?></td>
          </tr>
        </tbody>
      </table>
      <br>

      <table  width="100%" >
        <tbody>
          <tr >
            <td width="28%" height="35" class="bg-color" >First Name of Secretary </td>
            <td width="72%" height="35"><?php  echo ($sec_type =='firm') ? $sec['title'] : $sec['firstname'];?></td>
          </tr>
          <tr >
            <td width="28%" height="35" class="bg-color">Last Name of Secretary</td>
            <td width="72%" height="35"><?php echo ($sec_type =='firm') ? '' :  $sec['lastname']; ?></td>
          </tr>
          <tr>
            <td width="28%" height="60" class="bg-color" >Residential Address </td>
            <td width="72%" height="60" >
            <?php  echo $sec['localAddress1'].',<br/>'; ?>
            <?php echo ($sec['localAddress2']) ? $sec['localAddress2'].',<br/>' : ''; ?>
            <?php // echo $sec['city'].',<br/>'; ?>
            <?php echo '<strong>postcode: </strong>'.$sec['postcode']; ?>
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

              $o_date = $sec['date'];

             

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
            <td width="72%"><?php echo   ($sec_type =='firm') ? $sec['registration_no'] : $sec['regDate']; ?></td>
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
            <td width="72%"  height="50"><?php echo ($sec_type =='firm') ? $sec['title']  :  ( $sec['firstname'].' '.$sec['lastname'] );?></td>
          </tr>
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

      <strong><font>Presented by:</font></strong>

      <table  width="100%">
        <tbody>
          <tr>
            <td width="28%"  height="40" class="bg-color">Full Name </td>
            <td width="72%"  height="40"><?php echo $loginUser->first_name; ?> <?php echo $loginUser->last_name; ?></td>
          </tr>
          <tr  >
            <td width="28%" class="bg-color">Email Address</td>
            <td width="72%"><?php echo $loginUser->email; ?></td>
          </tr>
          <tr >
            <td width="28%" class="bg-color">Telephone No. </td>
            <td width="72%"><?php echo $loginUser->telephone; ?></td>
          </tr>
          <tr >
            <td width="28%" class="bg-color">Mobile No. </td>
            <td width="72%" ><?php echo $loginUser->mobile; ?></td>
          </tr>
          <tr >
            <td width="28%" height="60" class="bg-color">Address </td>
            <td width="72%" height="60" >

             <?php  echo $loginUserAddress->address1.',<br/>'; ?>
                                 <?php  echo ( $loginUserAddress->address2 ) ? $loginUserAddress->address2.',<br/>' : ''; ?>
                                 <?php // echo $loginUserAddress->city.',<br/>'; ?>
                                 <?php  // echo $loginUserAddress->postcode; ?>
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