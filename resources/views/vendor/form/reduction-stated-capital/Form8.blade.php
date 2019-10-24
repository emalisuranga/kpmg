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
            font-family: 'SegoeUI';
        }

        table{
            page-break-inside: avoid;
            table-layout:fixed;
        }

        font {
            margin-left: 0px;
            margin-right: 0px;
            font-size: 14px;
            font-family: 'SegoeUI';
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
            font-family: 'SegoeUI';

        }
    </style>
    </head>
    <body>
    <section class="form-body">
        <?php $img1 = public_path() . '/images/govlogo.jpg';?>
        <?php $img2 = public_path() . '/images/eroc.png';?>
        <table width="100%" style="border:0; padding:0;" autosize="1">
            <tr>
                <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{ $img1 }}" alt="gov_logo"></td>
                <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 8<br><br></b></span><p style="font-size: 13px; ">Notice of </p><p style="font-size:16px;"><b>REDUCTION OF STATED CAPITAL</b></p></td>
                <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 59(5))</td>
                <td width="20%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{ $img2 }}" alt="Logo EROC"></td>
            </tr>
			
            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">Section 59(5) of the Companies Act No.
                    7 of 2007</td>
            </tr>
        </table>
        <br>

        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr >
                <td width="28%" style="border: 0; padding:0" >Number of the company </td>
                    <td width="72%" class="bg-color">{{ $refId }}</td>
                </tr>
            </tbody>
        </table>
        <br>
        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="117.1pt" height="40" class="bg-color">Name of the Company</td>
                    <td width="392.7pt" height="40">{{ $CompanyName }}</td>
                </tr>
            </tbody>
        </table>
            <br>

		<table style="--primary-text-color: #212121; " width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="489pt" height="20" class="bg-color" align="left" >Stated capital prior to the reduction (a) </td>
                   <!-- <td width="128pt" height="20">&nbsp;</td> -->
					<td width="80pt" height="20" class="bg-color" align="center"> {{ $share_capital_amount }} </td>
                </tr>
                <tr>
                    <td width="489pt" height="20" class="bg-color" align="left"><br>Stated Capital pursuant to the reduction (b) </td>
                    <!-- <td width="128pt" height="20">&nbsp;</td> -->
					<td width="80pt" height="20" class="bg-color" align="center"> {{ $reduction_amount }} </td>
                </tr>
				<tr>
                    <td width="489pt" height="20" class="bg-color" align="left"><br>Amount of the reduction (a - b)  </td>
                   <!-- <td width="128pt" height="20">&nbsp;</td> -->
					<td width="80pt" height="20" class="bg-color" align="center"> {{ $reduction_capital_amount }} </td>
                </tr>
            </tbody>
        </table>
		<br>
		<font >The date of the special resolution passed by the company to reduce its Stated Capital</font>
        <br><br>
        <table  width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
                    <td height="30" align="right" style="border:0">Date:</td>
                    <td width="8%" style="border:0"></td>
                    <td width="5%">{{ $resolution_date[6] }}</td>
                    <td width="5%">{{ $resolution_date[7] }}</td>
                    <td width="10%"  style="border:0"></td>
                    <td width="5%">{{ $resolution_date[4] }}</td>
                    <td width="5%">{{ $resolution_date[5] }}</td>
                    <td width="10%" style="border:0"></td>
                    <td width="5%">{{ $resolution_date[0] }}</td>
                    <td width="5%">{{ $resolution_date[1] }}</td>
                    <td width="5%">{{ $resolution_date[2] }}</td>
                    <td width="5%">{{ $resolution_date[3] }}</td>
                    <td width="4%" style="border:0"></td>
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
		<table style="--primary-text-color: #212121; " width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="127pt" height="55" class="bg-color" align="left" >Signature of {{ $designation }}:</td>
                    <td width="382pt">&nbsp;</td>
                </tr>
                <tr>
                    <td width="127pt" height="60" class="bg-color" align="left">Full Name of {{ $designation }}:</td>
                    <td width="382pt">{{ $name }}</td>
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
                    <td width="229pt" height="40">{{ $username }}</td>
                  <!--  <td width="30pt"  height="40" rowspan="6" style="text-rotate: 90;" align="center" class="bg-color">Signature</td>
                    <td width="168pt" height="40"   rowspan="6"  ></td> -->
                </tr>

                <tr >
                    <td width="82pt"  class="bg-color"  height="40">E Mail Address</td>
                    <td width="229pt">{{ $email }}</td>
                </tr>

                <tr >
                    <td width="82pt" class="bg-color"  height="40">Telephone No</td>
                    <td width="229pt">{{ $telephonenumber }}</td>
                </tr>

                <tr >
                    <td width="82pt" class="bg-color"  height="40">Mobile No</td>
                    <td width="229pt" >{{ $mobile }}</td>
                </tr>

                <tr >
                    <td width="82pt" class="bg-color"  height="40">Address</td>
                    <td width="229pt">{{ $address }}</td>
                </tr>
                </tbody>
            </table>


        <br>

        <font >Note : This notice should be given to the Registrar-General of Companies, within 10 working days,where a company has reduced its stated capital</font>

		
        </section>
    </body>
</html>