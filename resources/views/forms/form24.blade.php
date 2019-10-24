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
            font-family: 'SegoeUI', sans-serif;
        }

        table{
            page-break-inside: avoid;
            table-layout:fixed;
            }

        font {
            margin-left: 0px;
            margin-right: 0px;
            font-size: 14px;
            font-family: 'SegoeUI',sans-serif;
            margin-bottom: 1px;
        }

        .bg-color {
            background: #D3D3D3;
        }

        .a {
            line-height: 12px;
            border-bottom: #000000;
            border-top: #000000;
            background: #dedcdc;
            position: relative;
        }

        body {
            margin-left: 2.5cm;
            font-family: 'SegoeUI',sans-serif;

        }
    </style>

    </head>

    <body>

    <section class="form-body">
        
        <table width="100%" style="border:0; padding:0;" autosize="1">
            <tr>
                <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 24<br><br><b>NOTICE BY OFF-SHORE COMPANY OF INTENTION TO CEASE <br>TO CARRY ON BUSINESS</b></p></td>
                <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 265)</td>
                <td width="20%" style="border:0; padding:0px;"><img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>
        
            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">Section 265 of the Companies Act No. 7 of 2007 </td>
            </tr>
        </table>

        <br>

         <table style="border: 0;" width="100%" autosize="1">
            <tbody>

                <tr><td style="border: 0;" width="100%" colspan="6">(Please note that the information in this form must be either typewritten or printed. It must not be handwritten. If there is insufficient space on the form to supply the information required attach a separate sheet containing the information set out in the prescribed format.)</td></tr>
                <tr>
                    <td style="border: 0;" height="10"></td>
                </tr>
                <tr >
                    <td width="23%" height="40" style="border: 0;"> 
                        <p style="padding-left: 9pt;text-indent: 0pt;text-align: left;">Company Number</p></td>
                    <td width="7%"  height="40" class="bg-color">&nbsp;<?php echo $regNo[0];?></td>
                    <td width="7%"  height="40" class="bg-color">&nbsp;<?php echo $regNo[1];?></td>
                    <td width="49%" height="40" class="bg-color">&nbsp;<?php echo substr($regNo, 2); ?></td>
					<td width="7%" height="40" class="bg-color">&nbsp;</td>
					<td width="7%" height="40" class="bg-color">&nbsp;</td>
                </tr>
            </tbody>
        </table>

        <br>

        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="23%" height="40" class="bg-color">
                        <p style="padding-left: 10pt;text-indent: 0pt;text-align: left;">Company Name</p>
                    </td>
                    <td width="77%" height="40">&nbsp; <?php echo $comName; ?>&nbsp;<?php echo $comPostfix; ?></td>
                </tr>
            </tbody>
        </table>

        <br>

        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="23%" height="40" class="bg-color">Country in which it is Incorporated</td>
                    <td width="77%" height="40">&nbsp; <?php echo $country; ?></td> 
                </tr>
            </tbody>
        </table>
		
		<br>
		
		<table  width="100%" style="border:0" autosize="1">
            <tbody>          
                <tr><td style="border:0">The above named Company will cease to carry on business on the under noted date</td></tr>
            </tbody>
        </table>

        <br>

        <table  width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
                    <td height="30" align="center" style="border:0">Date</td>
                    <td width="8%" style="border:0"    ></td>
                    <td width="5%"><?php echo $date[0]; ?></td>
                    <td width="5%"><?php echo $date[1]; ?></td>
                    <td width="10%"  style="border:0"   ></td>
                    <td width="5%"><?php echo $month[0]; ?></td>
                    <td width="5%"><?php echo $month[1]; ?></td>
                    <td width="10%" style="border:0"    ></td>
                    <td width="5%"><?php echo $year[0]; ?></td>
                    <td width="5%"><?php echo $year[1]; ?></td>
                    <td width="5%"><?php echo $year[2]; ?></td>
                    <td width="5%"><?php echo $year[3]; ?></td>
                    <td width="4%" style="border:0"    ></td>
                </tr>
                <tr>
                    <td width="30%" height="22"  style="border:0"   ></td>
                    <td width="10%" style="border:0" > </td>
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

        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="30%"  height="100" align="left" class="bg-color">Name of Authorized Representative </td>
                    <td width="70%"></td>
                   
                </tr>

                <tr> <td style="border:0"></td></tr>
                <tr>
                    <td width="30%" height="100"  align="left" class="bg-color">Signature of Authorized Representative</td>
                    <td width="70%">&nbsp; <?php echo $singning_party_name; ?></td>
                   
                </tr>
          </tbody>
        </table>
        
          <br>
    
        <table  width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
                    <td height="30" align="center" style="border:0">Date</td>
                    <td width="8%" style="border:0"    ></td>
                    <td width="5%"><?php echo $curentDate[0]; ?></td>
                    <td width="5%"><?php echo $curentDate[1]; ?></td>
                    <td width="10%"  style="border:0"   ></td>
                    <td width="5%"><?php echo $curentMonth[0]; ?></td>
                    <td width="5%"><?php echo $curentMonth[1]; ?></td>
                    <td width="10%" style="border:0"    ></td>
                    <td width="5%"><?php echo $curentYear[0]; ?></td>
                    <td width="5%"><?php echo $curentYear[1]; ?></td>
                    <td width="5%"><?php echo $curentYear[2]; ?></td>
                    <td width="5%"><?php echo $curentYear[3]; ?></td>
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

        <br><br>
        
		 <table style="--primary-text-color: #212121; " width="100%" style="border:0;" autosize="1">
            <tbody>
                <tr>
                    <td style="border:0;" colspan="4"><b>Presented by</b></td>
                </tr>
                
                <tr> 
                    <td width="82pt" height="40" class="bg-color">Full Name </td>
                    <td width="229pt" height="40">&nbsp;</td>
                    <td width="30pt"  height="40" rowspan="6" style="text-rotate: 90;" align="center" class="bg-color">Signature</td>
                    <td width="168pt" height="40" rowspan="6" ></td>
                </tr>

                <tr >
                    <td width="82pt"  class="bg-color"  height="40">E Mail Address</td>
                    <td width="229pt">&nbsp;</td>
                </tr>

                <tr >
                    <td width="82pt" class="bg-color"  height="40">Telephone No</td>
                    <td width="229pt">&nbsp;</td>
                </tr>

                <tr >
                    <td width="82pt" class="bg-color"  height="40">Mobile No</td>
                    <td width="229pt" >&nbsp;</td>
                </tr>

                <tr >
                    <td width="82pt" class="bg-color"  height="40">Address</td>
                    <td width="229pt">&nbsp;</td>
                </tr>
            </tbody>
        </table>
        
      <br>
      <br>
    </body>
</html>