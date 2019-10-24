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
                    <td width="10%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                    <td width="59%" style="border:0; font-size: 18px; padding-top:20px; padding-left:100px " align="center"><b>FORM 16<br><br></b></td>
                    <td width="13%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 149(2)(b))</td>
                    <td width="18%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
                </tr>
                <tr>
                    <td colspan="4" align="center" style="border:0; font-size:16px; padding:0;"><b>LOCATION OF ACCOUNTING RECORDS</b></td>
                </tr>
                <tr>
                    <td colspan="4"  style="border:0; padding:0px; font-size:13px; padding-left:203px;">Section 149(2)(b) of the Companies Act No. 7 of 2007
</td>
                </tr>
            </table>
            <br>

            <table style="border: 0;" width="100%" >
                    <tbody>
                        <tr>
                            <td width="28%" style="border: 0; padding:0" >Number of the company </td>
                            <td width="72%" >&nbsp;<?php echo $regNo;?></td>
                        </tr>
                    </tbody>
                </table>
                <br>
        
                <table style="border: 0;" width="100%" >
                    <tbody>
                        <tr>
                            <td width="28%" height="50" class="bg-color">Name of the company </td>
                            <td width="72%" height="50">&nbsp; <?php echo $comName; ?>&nbsp;<?php echo $comPostfix; ?></td>
                        </tr>
                    </tbody>
                </table>
            <br>

            <table width="100%" height="30">
                <tbody>
                    <tr height="20">
                        <td width="28%" class="bg-color">The address at which the accounting records or accounts and returns required under section 149(2)(a) are kept</td>
                        <td width="72%">&nbsp; <?php
                            $line1 = $address1 . ',';
                            $line2 = $address2 . ',';
                            $city = $city. ' city,';
                            if($district){
                                $district = $district. ' district,';
                            }
                            if($country == 'Sri Lanka'){
                                $province = $province. ' province,';
                            }
                            else{
                                $province = $province. ' state,';
                            }
                            
                            $post_code = $postcode;
                            $gn_division = $gn_division;
            
                            echo $line1 . $line2 .' '.$province.' '.$district.' '. $city . '  ' . $post_code . '  ' . $country;
            
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>

            <div style="text-align:justify">The date from which the accounting records or accounts and returns required under section
149(2)(a) have been kept at the above mentioned address
</div>
<br>

<table style="border: 0" width="100%" >
                <tbody>
                <tr>
                    <td height="20"  style="border: 0"></td>
                    <td height="20" style="border: 0"></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $day[0];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $day[1];?></td>
                    <td height="20" style="border: 0"></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $month[0];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $month[1];?></td>
                    <td height="20" style="border: 0"></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $year[0];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $year[1];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $year[2];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $year[3];?></td>
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
            <br/>
            <br>

            <table width="100%">
                <tbody>
                    <tr >
                        <td width="30%" height="80" class="bg-color">Signature of <?php echo $member[0]['designation']; ?></td>
                        <td width="70%" height="80"></td>
                    </tr>
                    <tr >
                        <td width="30%" height="50" class="bg-color">Full Name of <?php echo $member[0]['designation']; ?></td>
                        <td width="70%" height="50">&nbsp;<?php echo $member[0]['first_name']; ?>&nbsp;<?php echo $member[0]['last_name']; ?></td>
                    </tr>
                </tbody>
            </table>
            <br>

            <table style="border: 0" width="100%" >
                <tbody>
                <tr>
                    <td height="20"  style="border: 0"></td>
                    <td height="20" style="border: 0"></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $day1[0];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $day1[1];?></td>
                    <td height="20" style="border: 0"></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $month1[0];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $month1[1];?></td>
                    <td height="20" style="border: 0"></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $year1[0];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $year1[1];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $year1[2];?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo $year1[3];?></td>
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
            <br>

            <table style="--primary-text-color: #212121; " width="100%">
            <tbody>
                <tr  > 
                    <td width="16%" height="40" class="bg-color">Full Name </td>
                    <td width="45%" height="40">&nbsp;<?php
                        $firstanme = $first_name;
                        $lastname = $last_name;
        
                        echo $firstanme . ' ' . $lastname;
        
                        ?></td>
                    {{-- <td width="6%"  height="40" rowspan="4" class="a"><img width="20" height="80" src="{{  URL::to('/') }}/images/signature.png" alt="signature"></td>
                    <td width="33%" height="40" rowspan="4" ></td> --}}
                </tr>
                <tr >
                    <td width="16%"  class="bg-color" >Email address</td>
                    <td width="45%">&nbsp;<?php echo $email;?></td>
                </tr>
                <tr >
                    <td width="16%" class="bg-color" >Telephone No</td>
                    <td width="45%">&nbsp;<?php echo $telephone;?></td>
                </tr>
                <tr >
                    <td width="16%" class="bg-color">Mobile No</td>
                    <td width="45%">&nbsp;<?php echo $mobile;?></td>
                </tr>
            </tbody>
        </table>
        <br>
       
       <div style="text-align:justify">Note : This Notice should be given to the Registrar-General of Companies, at intervals not exceeding
periods of six months in terms of section 149 (2)(b) if the accounting records are not kept in Sri Lanka</div>
</body>
</html>