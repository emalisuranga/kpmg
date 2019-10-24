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
            font-family: sans-serif;

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
                <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 8<br><br></b></span><p style="font-size: 13px; ">Notice of </p><p style="font-size:16px;"><b>REDUCTION OF STATED CAPITAL</b></p></td>
                <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 59(5))</td>
                <td width="20%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>
			
            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">Section 59(5) of the Companies Act No.
                    7 of 2007</td>
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
                    <td width="117.1pt" height="40" class="bg-color">Name of the Company</td>
                    <td width="392.7pt" height="40"><?php echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?></td>
                </tr>
            </tbody>
        </table>
            <br>

		<table style="--primary-text-color: #212121; " width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="489pt" height="20" class="bg-color" align="left" >Stated capital prior to the reduction (a) </td>
                   <!-- <td width="128pt" height="20">&nbsp;</td> -->
					<td width="80pt" height="20" class="bg-color" align="center"> <?php echo $record->share_capital_amount; ?> </td>
                </tr>
                <tr>
                    <td width="489pt" height="20" class="bg-color" align="left"><br>Stated Capital pursuant to the reduction (b) </td>
                    <!-- <td width="128pt" height="20">&nbsp;</td> -->
					<td width="80pt" height="20" class="bg-color" align="center"> <?php echo $record->reduction_amount; ?> </td>
                </tr>
				<tr>
                    <td width="489pt" height="20" class="bg-color" align="left"><br>Amount of the reduction (a - b)  </td>
                   <!-- <td width="128pt" height="20">&nbsp;</td> -->
					<td width="80pt" height="20" class="bg-color" align="center"> <?php echo $record->reduction_capital_amount; ?> </td>
                </tr>
            </tbody>
        </table>
		<br>
		<font >The date of the special resolution passed by the company to reduce its Stated Capital</font>
        <br><br/>
        <?php
            $date =  strtotime($record->resalution_date);
            if($date) {
                $d = date('d', $date);
                $m = date('m', $date);
                $y = date('Y', $date);
            } else {
                $d = $m = $y = null;
            }
            

            ?>
           <table style="border: 0" width="100%" autosize="1" >
                <tbody>
                <tr>
                    <td height="20"  style="border: 0"></td>
                    <td height="20" style="border: 0"></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo isset($d[0]) ? $d[0] : '' ; ?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo isset($d[1]) ? $d[1] : '' ; ?></td>
                    <td height="20" style="border: 0"></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo isset($m[0]) ? $m[0] : '' ; ?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo isset($m[1]) ? $m[1] : '' ; ?></td>
                    <td height="20" style="border: 0"></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo isset($y[0]) ? $y[0] : '' ; ?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo isset($y[1]) ? $y[1] : '' ; ?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo isset($y[2]) ? $y[2] : '' ; ?></td>
                    <td height="20" width="4%" style="text-align:center;"><?php echo isset($y[3]) ? $y[3] : '' ; ?></td>
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
                    <td height="30" align="right" style="border:0">Date:</td>
                    <td width="8%" style="border:0"    ></td>
                    <td width="5%">{{ $data[6] }}</td>
                    <td width="5%">{{ $data[7] }}</td>
                    <td width="10%"  style="border:0"   ></td>
                    <td width="5%">{{ $data[4] }}</td>
                    <td width="5%">{{ $data[5] }}</td>
                    <td width="10%" style="border:0"    ></td>
                    <td width="5%">{{ $data[0] }}</td>
                    <td width="5%">{{ $data[1] }}</td>
                    <td width="5%">{{ $data[2] }}</td>
                    <td width="5%">{{ $data[3] }}</td>
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
		
		<table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
                <tbody>

                <tr>
                    <td style="border:0;"><b>Presented by:</b></td>
                </tr>
                
                <tr> 
                    <td width="82pt" height="40" class="bg-color">Full Name </td>
                    <td width="229pt" height="40"><?php echo $loginUser->first_name; ?> <?php echo $loginUser->last_name; ?></td>
                  <!--  <td width="30pt"  height="40" rowspan="6" style="text-rotate: 90;" align="center" class="bg-color">Signature</td>
                    <td width="168pt" height="40"   rowspan="6"  ></td> -->
                </tr>

                <tr >
                    <td width="82pt"  class="bg-color"  height="40">E Mail Address</td>
                    <td width="229pt"><?php echo $loginUser->email; ?></td>
                </tr>

                <tr >
                    <td width="82pt" class="bg-color"  height="40">Telephone No</td>
                    <td width="229pt"><?php echo $loginUser->telephone; ?></td>
                </tr>

                <tr >
                    <td width="82pt" class="bg-color"  height="40">Mobile No</td>
                    <td width="229pt" ><?php echo $loginUser->mobile; ?></td>
                </tr>

                <tr >
                    <td width="82pt" class="bg-color"  height="40">Address</td>
                    <td width="229pt">  <?php  echo $loginUserAddress->address1.',<br/>'; ?>
                            <?php  echo ( $loginUserAddress->address2 ) ? $loginUserAddress->address2.',<br/>' : ''; ?>
                            <?php  echo $loginUserAddress->city.',<br/>'; ?>
                            <?php  echo '<strong>postcode: </strong>'.$loginUserAddress->postcode; ?></td>
                </tr>
                </tbody>
        </table>


        <br>

        <font >Note : This notice should be given to the Registrar-General of Companies, within 10 working days,where a company has reduced its stated capital</font>

		
        </section>
    </body>
</html>