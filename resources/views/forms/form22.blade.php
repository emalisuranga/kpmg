<!DOCTYPE html>
    <head>
        <meta charset="UTF-8">
        <style>
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
            font-family: 'sans-serif';
        }

        table{
            page-break-inside: avoid;
            table-layout:fixed;
        }

        font {
            margin-left: 0px;
            margin-right: 0px;
            font-size: 14px;
            font-family: 'sans-serif';
            margin-bottom: 1px;
        }

        .bg-color {
            background: #b9b9b9;
        }

        font{
            font: #ffffff
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
            margin-left: 2.5cm;
            font-family: 'sans-serif';

        }
    </style>
    </head>
    <body>
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
    <section class="form-body">
     
        <table width="100%" style="border:0; padding:0;" autosize="1">
            <tr>
                <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 22<br><br></b></span><p style="font-size:16px;"><b>NOTICE</b></p></td>
                <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 246(1)) </td>
                <td width="20%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>
			
            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">(Under Section 246(1) of the Companies Act No.
                    7 of 2007)  </td>
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
        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="117.3pt" height="40" class="bg-color">Name of the Company</td>
                    <td width="392.7pt" height="40"><?php echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?></td>
                </tr>
            </tbody>
        </table>
            <br>

        <br>

        <?php
        $to_list = '';
        $to_list = ($record->shareholder_name_list) ? str_replace("\r", ",", $record->shareholder_name_list) : ''; 
        $to_list = str_replace("\n", ",", $to_list);

        ?>
		<font >To:&nbsp;&nbsp;&nbsp;<?php echo $to_list;?><br> <span style="font-size: 12px;"> (each of the shareholders of the outstanding shares)</span></font>
		
		<br>
		<br>
		
		<table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td style="border: 0" align="justify">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Whereas pursuant to an offer made to the holders of voting rights of
                    <?php echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?>, we/I* have acquired not less than ninety
percentum (90%) of the voting rights of the said Company within the last three (3) months.<br><br>
We/I do hereby give you notice in terms of Section 246(1) of the Companies Act No. 7 of
2007
of our/my desire to acquire the shares held by you in the said Company. </td>
                </tr>
            </tbody>
        </table>

<br>
<br>

		<table style="border: 0;" width="100%" autosize="1" >
            <tbody>
                <tr>
				
                    <td width="127.5pt" height="60" class="bg-color";>Signature </td>
                    <td width="382.5pt" height="60"></td>
                </tr>
				<tr>
					<td style = "border:0" width="100" colspan="2" > </td>
				</tr>
                <tr>
                    <td width="127.5pt" height="50" class="bg-color";>Name </td>
                    <td width="382.5pt" height="50"><?php echo $loginUser->first_name; ?> <?php echo $loginUser->last_name; ?></td>
                </tr>
				<tr>
					<td style = "border:0" width="100" colspan="2" > </td>
				</tr>
                <tr>
                    <td width="127.5pt" height="100" class="bg-color";>Address</td>
                    <td width="382.5pt" height="100">
                    <?php  echo ( $loginUserAddress->address2 ) ? $loginUserAddress->address2.',<br/>' : ''; ?>
                                 <?php // echo $loginUserAddress->city.',<br/>'; ?>
                                 <?php  // echo $loginUserAddress->postcode; ?>
                                 <?php  echo '<strong>postcode: </strong>'.$loginUserAddress->postcode; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>

        <?php
            $date_of = $record->date_of ? strtotime($record->date_of) : null;
            if($date_of) {
                $d = date('d', $date_of);
                $m = date('m', $date_of);
                $y = date('Y', $date_of);
            } else {
                $d = $m = $y = null;
            }
            

        ?>

        <table style="border: 0" width="100%" >
            <tbody>
            <tr>
                  <td height="30" align="right" style="border:0">&nbsp;</td>
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
        


		<br>
		<br>
		
		<table style="border-color: #FFFFFF;" width="100%" height="30" align="center" autosize="1">
            <tbody>
                <tr>
                    <td align="left" style="border:0;">Note : This notice should be given to the Registrar-General of Companies within 3 months of such acquisition in terms of Sec. 246(1).
					</td>
                </tr>
            </tbody>
        </table>
		<br>
		
		<table style="border-color: #FFFFFF;" width="100%" height="30" align="left" autosize="1">
            <tbody>
                <tr>
                    <td align="left" style="border:0;"><span style="font-size: 13px;">*  Delete what is not applicable
					</td>
                </tr>
            </tbody>
        </table>
		<br>
		<br>
        <br>
    </body>
</html>