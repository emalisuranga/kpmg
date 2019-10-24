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
                font-family: 'SegoeUI',sans-serif;
            }

            table{
            page-break-inside: avoid;
            table-layout:fixed;
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

            body {
                margin-left: 2.5cm;
                margin-right: 2.5cm;
                font-family: 'SegoeUI', sans-serif;

            }
        </style>
    </head>

    <body>

    <section class="form-body">

        <table width="100%" style="border:0; padding:0;" autosize="1">
            <tr>
                <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 39<br><br></b></span><p style="font-size: 13px; "></p><p style="font-size:16px;"><b>THE COMPANIES ACT NO. 7 of 2007<br>NOTICE OF A SPECIAL RESOLUTION 1</b></p></td>
                <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left"></td>
                <td width="20%" style="border:0; padding:0px;"><img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>    
            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;"></td>
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
                    <td width="117.1pt" height="40" class="bg-color">Name of the Company</td>
                    <td width="392.7pt" height="40"><?php echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?></td>
                </tr>
            </tbody>
        </table>
        
        <br>

		<table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                <?php

                $res_date = strtotime($record->resolution_passed_date);
                $court_d = date('d', $res_date);
                $court_y = date('Y', $res_date);
                $court_m = date("F",  $res_date);

                    ?>
                    <td style="border: 0; line-height:1.5em">The <?php echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?> Company forwards a printed copy of a Special Resolution passed by the company on <?php echo $court_d;?> day of <?php echo $court_m;?> <?php echo $court_y;?>. </td>
                </tr>
               
            </tbody>
        </table>

        <br>

		<table style="--primary-text-color: #212121; " width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="127pt" height="40" class="bg-color" align="left" >Signature of <?php echo $stakeholder_info['type'];?>:</td>
                    <td width="382pt">&nbsp;</td>
                </tr>
                <tr>
                    <td width="127pt" height="40" class="bg-color" align="left">Full Name of <?php echo $stakeholder_info['type'];?>:</td>
                    <td width="382pt"><?php echo $stakeholder_info['name'];?></td>
                </tr>
            </tbody>
        </table>
        
        <br>
        <?php
            $data = date('Ymd');
        ?>
		<table  width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
					<td width="15%" height="30" style="border: 0;">Date: </td>
                    <td width="8%" style="border:0"></td>
                    <td width="5%" style="text-align:center">{{ $data[6] }}</td>
                    <td width="5%" style="text-align:center">{{ $data[7] }}</td>
                    <td width="10%"  style="border:0"   ></td>
                    <td width="5%" style="text-align:center">{{ $data[4] }}</td>
                    <td width="5%" style="text-align:center">{{ $data[5] }}</td>
                    <td width="10%" style="border:0"    ></td>
                    <td width="5%" style="text-align:center">{{ $data[0] }}</td>
                    <td width="5%" style="text-align:center">{{ $data[1] }}</td>
                    <td width="5%" style="text-align:center">{{ $data[2] }}</td>
                    <td width="5%" style="text-align:center">{{ $data[3] }}</td>
                    <td width="4%" style="border:0"    ></td>
                </tr>
				<tr>
                    <td width="30%" height="22"  style="border:0"   ></td>
                    <td width="10%" style="border:0"    > </td>
                    <td colspan="2" >
                        <center>Day</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="2" >
                        <center>Month</center>
                    </td>
                    <td width="10%" style="border:0"    ></td>
                    <td colspan="4"  >
                        <center>Year</center>
                    </td>
                </tr>
				</tbody>
			</table>
       
        <br>

        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                
            </tbody>
		</table>
          
        <table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
                <tbody>

                <tr>
                    <td style="border:0;"><b>Presented by:</b></td>
                </tr>
                
                <!--<tr> 
                    <td width="82pt" height="40" class="bg-color">Full Name </td>
                    <td width="229pt" height="40"><?php // echo $loginUser->first_name; ?> <?php //echo $loginUser->last_name; ?></td>
                    <td width="30pt"  height="40" rowspan="6" style="text-rotate: 90;" align="center" class="bg-color">Signature</td>
                    <td width="168pt" height="40" rowspan="6" ></td>
                </tr>-->

                 <tr >
                    <td width="112pt"  class="bg-color"  height="40">Full name</td>
                    <td width="357pt"><?php  echo $loginUser->first_name; ?> <?php echo $loginUser->last_name; ?></td>
                </tr>

                <tr >
                    <td width="112pt"  class="bg-color"  height="40">E Mail Address</td>
                    <td width="357pt"><?php echo $loginUser->email; ?></td>
                </tr>

                <tr >
                    <td width="112pt" class="bg-color"  height="40">Telephone No</td>
                    <td width="357pt"><?php echo $loginUser->telephone; ?></td>
                </tr>

                <tr >
                    <td width="112pt" class="bg-color"  height="40">Mobile No</td>
                    <td width="357pt" ><?php echo $loginUser->mobile; ?></td>
                </tr>

                <tr >
                    <td width="112pt" class="bg-color"  height="40">Address</td>
                    <td width="357pt">
                    <?php  echo $loginUserAddress->address1.',<br/>'; ?>
                            <?php  echo ( $loginUserAddress->address2 ) ? $loginUserAddress->address2.',<br/>' : ''; ?>
                            <?php  echo $loginUserAddress->city.',<br/>'; ?>
                            <?php  echo '<strong>postcode: </strong>'.$loginUserAddress->postcode; ?>
                    </td>
                </tr>
                </tbody>
            </table>
        
        <br>

		<table style="border: 0;" autosize="1">
		    <tr>
                <td style="border: 0;"><b>NOTE</b></td>
            </tr>

            <tr>
                <td style="border: 0;">1. The notice should be sent within 10 days after passing of the Special Resolution thereof (Including that passed under Section 15(2) but excluding that passed under section 8(1)</td>
            </tr>
            <tr>
                <td style="border: 0;">2. Resolution: - Text of the resolution must be printed or printed copy annexed hereto and be certified by a Director / Secretary </td>
            </tr>
            

		</table>
    
    </section>
    </body>
</html>