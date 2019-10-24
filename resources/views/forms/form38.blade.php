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

        font {
            margin-left: 0px;
            margin-right: 0px;
            font-size: 14px;
            font-family: 'sans-serif';
            margin-bottom: 1px;
        }

        table{
            page-break-inside: avoid;
            table-layout:fixed;
        }

        .bg-color {
            background:  #b9b9b9;
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
    <section class="form-body">
        {{-- <!-- {{ $foo }} --> --}}
        <table width="100%" style="border:0; padding:0;" autosize="1">
            <tr>
                <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 38<br><br></b></span><p style="font-size:16px;"><b>REGISTERED OVERSEAS COMPANY OF CEASING TO HAVE A PLACE <br>
OF BUSINESS </b></p></td>
                <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 496 ) </td>
                <td width="20%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>
			
            
            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">In terms of Section  496 of the Companies Act No. 7 of 2007</td>
            </tr>
        </table>
        <br>
		
		<table width="100%" style="border:0; padding:0px;" autosize="1">
			<tr>
				<td style="border:0; padding:0px;" align="center" ><span style="font-size: 15px;" align= "center"><i> Please note that the information in this form must be either typewritten or printed. It must not be <br>
handwritten. If there is insufficient space on the form to supply the information required attach a <br>
separate sheet containing the information set out in the prescribed format.
 <i></span></td>
			</tr>
		</table>
<br>
        
       
<table style="border: 0;" width="100%" autosize="1">
    <tbody>
        <tr >
            <td width="117.3pt" height="40" style="border: 0;">Number of the Company</td>
            <td width="35.7pt"  height="40" >&nbsp;<?php echo $regNo[0];?></td>
            <td width="35.7pt"  height="40" >&nbsp;<?php echo $regNo[1];?></td>
            <td width="249.9pt" height="40" >&nbsp;<?php echo substr($regNo, 2); ?></td>
            <td width="35.7pt" height="40" >&nbsp;</td>
            <td width="35.7pt" height="40" >&nbsp;</td>
        </tr>
    </tbody>
</table>

        <br>

        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="117.1pt" height="40" class="bg-color">Name of the Company</td>
                    <td width="392.7pt" height="40">  <?php echo $comName; ?>&nbsp;<?php echo $comPostfix; ?></td>
                </tr>
            </tbody>
        </table>

        <br>

        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="117.1pt" height="40" class="bg-color">Country in which it is 
Incorporated 
 </td>
                    <td width="392.7pt" height="40"> <?php echo $country; ?></td>
                </tr>
            </tbody>
        </table>

        <br>

        <table  width="100%" style="border:0" autosize="1">
            <tbody>          
                <tr><td style="border:0">The above named Company ceased to have a place of business in Sri Lanka on:</td></tr>
            </tbody>
        </table>

        <br>

        {{-- <span style="font-size: 14px;" align="left" height="40">The above named Company ceased to have a place of business in Sri Lanka on:</span>
        <br> --}}
        
        <table style="border: 0" width="100%" autosize="1">
            <tbody> 
            <tr>
                <td height="30" align="center" style="border:0">Date</td>
                <td height="20" style="border: 0"></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $date[0]; ?></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $date[1]; ?></td>
                <td height="20" style="border: 0"></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $month[0]; ?></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $month[1]; ?></td>
                <td height="20" style="border: 0"></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $year[0]; ?></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $year[1]; ?></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $year[2]; ?></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $year[3]; ?></td>
            </tr>
            <tr>
            <td width="24%" height="5" style="border: 0" align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
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

        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                
                <tr>
                    <td style="border:0; padding:0px;"width="127.5pt" height="75">Signature of the Authorised Person</td>
                    <td width="382.5pt" height="50"> <?php echo $singning_party_name; ?></td>
                </tr>
            </tbody>
        </table>
		<br>

        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td style="border:0; padding:0px;"width="127.5pt" height="60">Name of the Authorised person 
</td>
                    <td width="382.5pt" height="30"> </td>
                </tr>
            </tbody>
        </table>
		<br>


        <table style="border: 0" width="100%" >
            <tbody>
            <tr>
                <td height="30" align="center" style="border:0">Date</td>
                <td height="20" style="border: 0"></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $curentDate[0]; ?></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $curentDate[1]; ?></td>
                <td height="20" style="border: 0"></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $curentMonth[0]; ?></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $curentMonth[1]; ?></td>
                <td height="20" style="border: 0"></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $curentYear[0]; ?></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $curentYear[1]; ?></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $curentYear[2]; ?></td>
                <td height="20" width="4%" style="text-align:center;"><?php echo $curentYear[3]; ?></td>
            </tr>
            <tr>
            <<td width="30%" height="22"  style="border:0"   ></td>
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
		

<table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
                <tbody>

                <tr>
                    <td style="border:0;"><b>Presented by:</b></td>
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



    </body>
</html>