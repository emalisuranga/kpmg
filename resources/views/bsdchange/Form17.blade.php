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

        /* .a> h6 {
            float: left;
            transform: rotate(-90deg);
            -webkit-transform: rotate(-90deg);  
            -ms-transform: rotate(-90deg); 
       

        } */

        body {
            margin-left: 2.5cm;
            font-family: 'SegoeUI';

        }
    </style>
    </head>
    <body>
    <section class="form-body">
        <table width="100%" style="border:0; padding:0;" autosize="1">
            <tr>
                <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 17<br><br></b></span><p style="font-size: 13px; ">Notice of </p><p style="font-size:16px;"><b>ADOPTION OR CHANGE OF BALANCE SHEET DATE </b></p></td>
                <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 171(6))</td>
                <td width="20%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>
			
            
            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">Section 171(6) of the Companies Act No.
                    7 of 2007</td>
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

        <table  width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
                    <td width="40%" height="52" align="left" rowspan="2" style="border:0">Balance sheet date of the<br> Company</td>
                    <td width="8%" height="30" style="border:0"></td>
                    <td width="5%"><?php if(isset($preday)){ echo $preday[0];

                    } ?></td>
                    <td width="5%"><?php if(isset($preday)){ echo $preday[1];

                    }?></td>
                    <td width="10%" style="border:0"></td>
                    <td width="5%"><?php if(isset($premonth)){ echo $premonth[0];

                    } ?></td>
                    <td width="5%"><?php if(isset($premonth)){ echo $premonth[1];

                    }?></td>
                    <td width="10%"  colspan="5" style="border:0"></td>
                </tr>
                <tr>
                    <td width="10%" style="border:0"></td>
                    <td colspan="2" class="bg-color" ><center>Day</center></td>
                    <td width="10%" style="border:0"></td>
                    <td colspan="2" class="bg-color"><center>Month</center></td>
                    <td width="10%" style="border:0"></td>
                    <td colspan="4" style="border:0"></td>
                </tr>
            </tbody>
        </table>

        <br>

        <table  width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
                    <td width="40%" height="52" align="left" rowspan="2" style="border:0">Proposed balance sheet date of the Company</td>
                    <td width="8%" height="30" style="border:0"></td>
                    <td width="5%"><?php if(isset($day)){ echo $day[0];

                    } ?></td>
                    <td width="5%"><?php if(isset($day)){ echo $day[1];

                    }?></td>
                    <td width="10%" style="border:0"></td>
                    <td width="5%"><?php if(isset($month)){ echo $month[0];

                    } ?></td>
                    <td width="5%"><?php if(isset($month)){ echo $month[1];

                    }?></td>
                </tr>
                <tr>
                    <td width="10%" style="border:0"></td>
                    <td colspan="2" class="bg-color" ><center>Day</center></td>
                    <td width="10%" style="border:0"></td>
                    <td colspan="2" class="bg-color"><center>Month</center></td>
                    <td width="10%" style="border:0"></td>
                    <td colspan="4" style="border:0"></td>
                </tr>

                <tr>
                    <td width="40%" style="border:0" valign="top"><span style="font-size: 10px;">(to be completed if this form is
                        presented to inform the change of balance sheet date) </td>   
                </tr>
            </tbody>
        </table>
	
		<br>
	
		<table width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
                    <td width="80%" align="left" rowspan="2" height="30" style="border:0">The calendar year from which the balance sheet date/ proposed balance sheet date* above mentioned shall have effect.</td>
                    <td width="5%" height="30" ><?php echo $effectedyear[0];?></td>
                    <td width="5%"><?php echo $effectedyear[1];?></td>
                    <td width="5%"><?php echo $effectedyear[2];?></td>
                    <td width="5%"><?php echo $effectedyear[3];?></td>
                </tr>

                <tr>
                    <td colspan="4" class="bg-color"><center>Year</center></td>
                </tr>
            </tbody>
        </table>

		<br>

		<table style="--primary-text-color: #212121; " width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="127pt" height="75" class="bg-color" align="left" >Signature of <?php echo $member[0]['designation']; ?> </td>
                    <td width="382pt">&nbsp;</td>
                </tr>
                <tr>
                    <td width="127pt" height="60" class="bg-color" align="left">Full Name of <?php echo $member[0]['designation']; ?> </td>
                    <td width="382pt"><?php echo $member[0]['first_name']; ?>&nbsp;<?php echo $member[0]['last_name']; ?></td>
                </tr>
            </tbody>
        </table>
		<br>
		 <table  width="100%" height="50.4" style="border:0" autosize="1"> 
            <tbody>
                <tr>
                    <td height="30" align="right" style="border:0">Date:</td>
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


        
		<table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
                <tbody>

                <tr>
                    <td style="border:0;"><b>Presented by:</b></td>
                </tr>
                
                <tr> 
                    <td width="82pt" height="40" class="bg-color">Full Name </td>
                    <td width="229pt" height="40"><?php
                        $firstanme = $first_name;
                        $lastname = $last_name;
        
                        echo $firstanme . ' ' . $lastname;
        
                        ?></td>
                    {{-- <td width="30pt"  height="40" rowspan="6" style="text-rotate: 90;" align="center" class="bg-color">Signature</td>
                    <td width="168pt" height="40" rowspan="6" ></td> --}}
                </tr>

                <tr >
                    <td width="82pt"  class="bg-color"  height="40">E Mail Address</td>
                    <td width="229pt"><?php echo $email;?></td>
                </tr>

                <tr >
                    <td width="82pt" class="bg-color"  height="40">Telephone No</td>
                    <td width="229pt"><?php echo $telephone;?></td>
                </tr>

                <tr >
                    <td width="82pt" class="bg-color"  height="40">Mobile No</td>
                    <td width="229pt" ><?php echo $mobile;?></td>
                </tr>
                </tbody>
            </table>

    </body>
</html>