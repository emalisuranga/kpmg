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
            font-family: 'SegoeUI', sans-serif;
        }
        table{
                page-break-inside: avoid;
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

<body>
<section class="form-body">
        <header class="form-header">
            <table width="100%" style="border:0; padding:0;">
                <tr>
                    <td width="10%" style="border:0; padding:0px;" rowspan="3"><img width="100" height="100" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                    <td width="67%" style="border:0; font-size: 20px; padding-top:20px; padding-left:105px " align="center"><b>FORM 6</b></td>
                    <td width="13%" style="border:0; padding:0px; font-size: 12px;" align="left">(Section 223(2))</td>
                    <td width="10%" style="border:0; padding:0px;" rowspan="3"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
                </tr>
                <tr>
                    <td width="69%" style="border:0; font-size: 13px; padding-top:0px; padding-left:5px " align="center" colspan="2">Notice of</td>               
                </tr>
                <tr>
                    <td  style="border:0; font-size:15px; padding:0; padding-left:210px " colspan="2"><b>ISSUE OF SHARES</b></td>
                </tr>
                <tr>
                    <td colspan="4"  style="border:0; padding:0px; font-size:13px; padding-left:230px;" colspan="4">Section 51(4)(a) of the Companies Act No. 7 of 2007</td>
                </tr>
            </table>
            <br>

             <table style="border: 0;" width="100%" >
                <tbody>
                    <tr>
                        <td width="25%" height="35" style="border: 0;">No. of Company</td>
                        <td width="7%" height="35">&nbsp;<?php echo $comReg[0];?></td>
                        <td width="7%" height="35">&nbsp;<?php echo $comReg[1];?></td>
                        <td width="61%" height="35">&nbsp;<?php echo substr($comReg, 2); ?></td>
                    </tr>
                </tbody>
            </table>
            <br>

            <table width="100%" >
                <tbody>
                    <tr>
                        <td width="25%" height="50" class="bg-color">Company Name </td>
                        <td width="75%" height="50">&nbsp; <?php echo $comName; ?></td>
                    </tr>
                </tbody>
            </table>
            <br>

            <span style="font-size: 14px;">Set out in the table below are particulars of the issue of shares by the above company</span>
           
            <table width="100%">
                <tr>
                    <td width="31%" align="center" class="bg-color">Date of issue</td>
                    <td width="31%" align="center" class="bg-color">Number of Shares</td>
                    <td width="38%" align="center" class="bg-color">Consideration or its value determined as Provided in Section 58(2) </td>
                </tr>
                <tr>
                    <td width="32%" height="30"><?php echo $date_of_issue; ?></td>
                    <td width="32%" height="30"><?php echo $number_of_shares; ?></td>
                    <td width="36%" height="30"><?php echo $consideration; ?> </td>
                </tr>
            </table>
            <br>

            <span style="font-size: 14px;">[Attach particulars of shareholders with their full names, addresses, NIC Nos/Passport Nos* and also a
copy of terms of issue, approved under section 51(2) if any (to be declared in terms of section 51(4(b))]</span>

            <table width="100%" >
                <tbody>
                    <tr>
                        <td width="70%" height="50"  align="right" class="bg-color">Stated Capital prior to this issue </td>
                        <td width="25%" height="50" align="right" >&nbsp; <?php echo $stated_capital_prior; ?></td>
                        <td width="5%" height="50" align="right" class="bg-color">(a)</td>
                    </tr>
                    <tr>
                        <td width="70%" height="50" align="right"  class="bg-color">The consideration for which or its value determined as provided in section 58(2) for which the shares were issued in this issue </td>
                        <td width="25%" height="50" align="right" >&nbsp; <?php echo $consideration; ?></td>
                        <td width="5%" height="50" align="right" class="bg-color" >(b)</td>
                    </tr>
                    <tr>
                        <td width="70%" height="50"  align="right" class="bg-color">Stated Capital following this issue (a+b=c) </td>
                        <td width="25%" height="50" align="right" >&nbsp; <?php echo $stated_capital_after; ?></td>
                        <td width="5%" height="50" align="right" class="bg-color">(c)</td>
                    </tr>
                </tbody>
            </table>
            <br>

            <table  width="100%">
            <tbody>
                <tr>
                    <td width="30%"  style="font-size:12px;" height="80" align="center" class="bg-color">Signature of Director/Secretary </td>
                    <td width="70%"  style="font-size:12px;" height="80"></td>
                </tr>
                <tr  >
                    <td width="30%" style="font-size:12px;" height="60" align="center" class="bg-color">Full Name of Director/ Secretary</td>
                    <td width="70%" style="font-size:12px;" height="60"></td>
                </tr>
            </tbody>
        </table>
        <br>

        <table  width="100%" height="50.4" style="border:0">
            <tbody>
                <tr>
                    <td height="25" align="right" style="border:0">Date:</td>
                    <td width="8%" style="border:0"    ></td>
                    <td width="5%"><?php echo $day[0];?></td>
                    <td width="5%"><?php echo $day[1];?></td>
                    <td width="10%"  style="border:0"   ></td>
                    <td width="5%"><?php echo $month[0];?></td>
                    <td width="5%"><?php echo $month[1];?></td>
                    <td width="10%" style="border:0"    ></td>
                    <td width="5%"><?php echo $year[0];?></td>
                    <td width="5%"><?php echo $year[1];?></td>
                    <td width="5%"><?php echo $year[2];?></td>
                    <td width="5%"><?php echo $year[3];?></td>
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

        <span>Presented by</span>

<table  width="100%">
    <tbody>
    <tr>
        <td width="20%"  height="40" class="bg-color">Full Name </td>
        <td width="80%"  height="40"><?php
                    $firstanme = $first_name;
                    $lastname = $last_name;
    
                    echo $firstanme . ' ' . $lastname;
    
                    ?></td>
    </tr>
    <tr  >
        <td width="20%" class="bg-color">Email Address</td>
        <td width="80%"><?php echo $email;?></td>
    </tr>
    <tr >
        <td width="20%" class="bg-color">Telephone No. </td>
        <td width="80%"><?php echo $telephone;?></td>
    </tr>
    <tr >
        <td width="20%" class="bg-color">Mobile No. </td>
        <td width="80%" ><?php echo $mobile;?></td>
    </tr>
    </tbody>
</table>
<br>

</body>

</html>