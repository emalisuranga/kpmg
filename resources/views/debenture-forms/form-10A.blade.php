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
                    <td width="60%" style="border:0; font-size: 18px; padding-top:20px; padding-left:150px " align="center"><b>FORM 10A<br><br></b></td>
                    <td width="12%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 102( 7 ))</td>
                    <td width="18%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
                </tr>
                <tr>
                    <td colspan="4" align="center" style="border:0; font-size:16px; padding:0; text-align:justify"><b>PARTICULARS OF AN ISSUE OF DEBENTURES IN A SERIES BY A COMPANY REGISTERED IN SRI LANKA  </b></td>
                </tr>
                <tr>
                    <td colspan="4"  style="border:0; padding:0px; font-size:13px; padding-left:230px;">Section 102( 7 ) of the  Companies Act No. 7 of 2007
</td>
                </tr>
            </table>
            <br>

            <table style="border: 0;" width="100%" >
                <tbody>
                    <tr>
                        <td width="30%" height="35" style="border: 0;">Number of the Company </td>
                        <td width="7%" height="35">&nbsp;<?php echo $comReg[0];?></td>
                        <td width="7%" height="35">&nbsp;<?php echo $comReg[1];?></td>
                        <td width="56%" height="35">&nbsp;<?php echo substr($comReg, 2); ?></td>
                    </tr>
                </tbody>
            </table>
            <br>


            <table width="100%" height="30">
                <tbody>
                    <tr>
                        <td width="30%" height="50" class="bg-color">Name of the Company </td>
                        <td width="70%" height="50">&nbsp; <?php echo $comName; ?></td>
                    </tr>
                </tbody>
            </table>
            <br>

            <table width="100%" style="border-bottom:0; border-right:0" >
                <tr>
                    <td width="1.20in" align="center" rowspan="2">Total amount secured by the whole series of Debentures</td>
                    <td width="0.71in" align="center" colspan="2">Date and amount of each Issue of the series</td>
                   
                    <td width="1.33in" align="center" rowspan="2">Dates of the Resolutions authorizing the Issue of the series</td>
                    <td width="0.71in" align="center" rowspan="2">Date of the Covering Deed if any</td>
                    <td width="1.03in" align="center" rowspan="2">General description of the Property Charged</td>
                    <td width="0.71in" align="center" rowspan="2">Names of the Trustees if any for the Debenture Holders</td>
                    <tr>
                        <td>Date</td>
                        <td>Amount</td>
                    </tr>
                <tr>
                <?php 
                foreach($debenture as $d ){
                    ?>
                <tr>
                    <td width="0.71in" height="50"><?php echo $d['total_amount_secured'];?></td>
                    <td width="0.71in" height="50"><?php echo $d['date_of_issue'];?></td>
                    <td width="1.41in" height="50"><?php echo $d['amount'];?></td>
                    <td width="0.71in" height="50"><?php echo $d['date_of_resolution'];?></td>
                    <td width="1.41in" height="50"><?php echo $d['date_of_covering_dead'];?></td>
                    <td width="0.71in" height="50"><?php echo $d['description'];?></td>
                    <td width="1.41in" height="50"><?php echo $d['name_of_trustees'];?></td>
                <tr>
                <?php
                        }  
                        ?>
            </table>
            <br>

            <div style="text-align:justify">A true copy of the deed / one of the debentures certified by a Director / Secretary of the Company / an Attorney-at-Law is annexed</div>
            <br>

            <table width="100%">
                <tbody>
                    <tr >
                        <td width="30%" height="80" class="bg-color">Signature of the {{$signing_party_designation}}</td>
                        <td width="70%" height="80"></td>
                    </tr>
                    <tr >
                        <td width="30%" height="50" class="bg-color">Full Name of the {{$signing_party_designation}}</td>
                        <td width="70%" height="50">{{$signing_party_name}}</td>
                    </tr>
                </tbody>
            </table>
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

            <strong><span>Presented by:</span></strong>

<br/>
<table width="100%">
   <tbody>
       <tr >
           <td width="30%"  height="40" class="bg-color">Full Name </td>
           <td width="70%" height="40" ><?php echo $first_name;?>&nbsp;<?php echo $last_name;?></td>
       </tr>
       <tr>
           <td width="30%" class="bg-color">Email Address</td>
           <td width="70%"><?php echo $email;?></td>
       </tr>
       <tr>
           <td width="30%" class="bg-color">Telephone No. </td>
           <td width="70%"><?php echo $telephone;?></td>
       </tr>
       <tr>
           <td width="30%" class="bg-color" style="padding-top:10px">Mobile No. </td>
           <td width="70%"><?php echo $mobile;?></td>
       </tr>
       <!-- <tr>
           <td width="30%" height="60" class="bg-color">Address </td>
           <td width="70%" height="60">        
           </td>
       </tr> -->
   </tbody>
</table>
<br>

<div>Note :</div>
<div style="text-align:justify">1)	This Form should be filed within Fifteen working days from the date of execution of the deed containing the  charge or if  there is no such deed,  from the date of execution of  any debentures of the series
</div>
<div>2)	Where more than one issue is made of debentures in the series the particulars in this Form should be sent to the Registrar General of Companies in terms of the proviso to sec.102 (7).  -</div>

</body>
</html>