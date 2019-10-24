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
            font-family: sans-serif;

        }
        </style>
    </head>

    <body>
    <section class="form-body">
        <header class="form-header">
            <table width="100%" style="border:0; padding:0;">
                <tr>
                    <td width="10%" style="border:0; padding:0px;" ><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                    <td width="67%" style="border:0; font-size: 20px; padding-top:20px; padding-left:105px " align="center"><b>FORM 13<br></b></td>
                    <td width="13%" style="border:0; padding:0px; font-size: 12px;" align="left">(Section 114(2))</td>
                    <td width="10%" style="border:0; padding:0px;" > <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
                </tr>
                <!-- <tr>
                    <td width="69%" style="border:0; font-size: 12px; padding-top:5px; padding-left:5px " align="center" colspan="2">Notice of</td>               
                </tr> -->
                <tr>
                    <td colspan="4" align="center" style="border:0; font-size:20px; padding:0;" colspan="4"><b>NOTICE OF COMPANY’S <br>
                    CHANGE OF REGISTERED OFFICE ADDRESS</b></td>
                </tr>
                <tr>
                    <td colspan="4"  style="border:0; padding:0px; font-size:13px; padding-left:230px;" colspan="4">Section 114(2) of the Companies Act No. 7 of 2007</td>
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

            <table width="100%" >
                <tbody>
                    <tr>
                        <td width="25%" height="50" class="bg-color">Address of New Registered Office</td>
                        <td width="75%" height="50"><?php
                            // $line1 = $address1 . ',';
                            // $line2 = $address2 . ',';
                            // $city = $city;
                            // $district = $district;
                            // $province = $province;
                            // $post_code = $postcode;
                            $gn_division = $gn_division;

                            $line1 = $address1.',<br/>';
                            $line2 = ($address2) ? $address2. ',<br/>' : '';
                            // $city = $company_address['city']. ',<br/>';
                            $city = '';
                            $district = $district;
                            $province = $province;
                            $post_code = '<strong>postcode: </strong>'.$postcode;
            
                            echo $line1 . $line2 . $city . $post_code;
            
                            ?></td>
                    </tr>
                </tbody>
            </table>
            <br>

            <table  width="100%" height="50.4" style="border:0">
            <tbody>
                <tr>
                    <td width="33%" height="22"  style="border:0; font-size:12px;" rowspan="2">The change in the registered office of the company takes effect on :</td>
                    <td width="3%" style="border:0"  height="22"  ></td>
                    <td width="5%" height="22"><?php echo $day[0];?></td>
                    <td width="5%" height="22"><?php echo $day[1];?></td>
                    <td width="10%"  style="border:0"  height="22"  ></td>
                    <td width="5%" height="22"><?php echo $month[0];?></td>
                    <td width="5%" height="22"><?php echo $month[1];?></td>
                    <td width="10%" style="border:0"   height="22"  ></td>
                    <td width="5%" height="22"><?php echo $year[0];?></td>
                    <td width="5%" height="22"><?php echo $year[1];?></td>
                    <td width="5%" height="22"><?php echo $year[2];?></td>
                    <td width="5%" height="22"><?php echo $year[3];?></td>
                    <td width="4%" style="border:0"  height="22"   ></td>
                </tr>
                <tr>
                    <!-- <td width="30%" height="22"  style="border:0"   ></td> -->
                    <td width="3%" style="border:0"  height="22"  > </td>
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

        <span><u>Note: </u></span>
        <span style="font-size: 14px;">The date on which the change in registered office takes effect must be at least 5 working days after the date on which this notice is received by the Registrar-General of Companies</span>
        <br>

        <table  width="100%">
            <tbody>
                <tr>
                    <td width="20%"  height="80" class="bg-color">Signature of <?php echo $memDesignation; ?> </td>
                    <td width="80%"  height="80"></td>
                </tr>
                <tr  >
                    <td width="20%" height="80" class="bg-color">Full Name of <?php echo $memDesignation; ?> </td>
                    <td width="80%" height="80"> <?php echo $memFirstName; ?></td>
                </tr>
            </tbody>
        </table>
        <br>

        <table  width="100%" height="50.4" style="border:0">
            <tbody>
                <tr>
                    <td height="25" align="right" style="border:0">Date:</td>
                    <td width="8%" style="border:0"    ></td>
                    <td width="5%"><?php echo $day1[0];?></td>
                    <td width="5%"><?php echo $day1[1];?></td>
                    <td width="10%"  style="border:0"   ></td>
                    <td width="5%"><?php echo $month1[0];?></td>
                    <td width="5%"><?php echo $month1[1];?></td>
                    <td width="10%" style="border:0"    ></td>
                    <td width="5%"><?php echo $year1[0];?></td>
                    <td width="5%"><?php echo $year1[1];?></td>
                    <td width="5%"><?php echo $year1[2];?></td>
                    <td width="5%"><?php echo $year1[3];?></td>
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
                <td width="80%"> <?php echo $email;?></td>
            </tr>
            <tr >
                <td width="20%" class="bg-color">Telephone No. </td>
                <td width="80%"> <?php echo $telephone;?></td>
            </tr>
            <tr >
                <td width="20%" class="bg-color">Mobile No. </td>
                <td width="80%" > <?php echo $mobile;?></td>
            </tr>
            </tbody>
        </table>
        <br>

        <span>Notice should be given to the Registrar General of Companies, the change of registered office and the effect must be at least 5 working days after the notice received.</span>


    </body>

</html>