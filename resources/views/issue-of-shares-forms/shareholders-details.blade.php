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
                    <td colspan="4" align="center" style="border:0; font-size:15px; padding:0;" colspan="4"><b>CURRENT ISSUE OF SHARES</b></td>
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

            <span>current issue of shares - shareholders induvidusl details</span>

            <?php 
                foreach($companyMembersarray as $s ){
                    ?>

            <table  width="100%">
                <tbody>
                <tr>
                    <td width="20%"  height="40" class="bg-color">Full Name </td>
                    <td width="80%"  height="40"><?php
                                $firstanme = $s['fname'];
                                $lastname = $s['lname'];
                
                                echo $firstanme . ' ' . $lastname;
                
                                ?></td>
                </tr>

            <?php
                    if($s['nic']){
                        ?>
                <tr>
                    <td width="20%" class="bg-color">Nic</td>
                    <td width="80%"><?php echo $s['nic'];?></td>
                </tr>
            <?php
                    }
                    ?>

                
            <?php
                    if($s['passport']){
                        ?>
                <tr  >
                    <td width="20%" class="bg-color">Passport</td>
                    <td width="80%"><?php echo $s['passport'];?></td>
                </tr>
            <?php
                    }
                    ?>
                
                <tr >
                    <td width="20%" class="bg-color">Current shares </td>
                    <td width="80%"><?php echo $s['currentshares'];?></td>
                </tr>
                <tr >
                    <td width="20%" class="bg-color">New shares </td>
                    <td width="80%" ><?php echo $s['newshares'];?></td>
                </tr>
                <tr >
                    <td width="20%" class="bg-color">Total shares </td>
                    <td width="80%" ><?php echo $s['totalshares'];?></td>
                </tr>
                </tbody>
            </table>
            <br>
            <?php
                        }  
                        ?>

            
        <br>

        <span>current issue of shares - shareholders firm details</span>

        <?php 
            foreach($companyFirmsarray as $s ){
                ?>

        <table  width="100%">
            <tbody>
            <tr>
                <td width="20%"  height="40" class="bg-color">Company Name </td>
                <td width="80%"  height="40"><?php echo $s['name'];?></td>
            </tr>

            <tr>
                <td width="20%"  height="40" class="bg-color">Registration no </td>
                <td width="80%"  height="40"><?php echo $s['regno'];?></td>
            </tr>
            
            <tr >
                <td width="20%" class="bg-color">Current shares </td>
                <td width="80%"><?php echo $s['currentshares'];?></td>
            </tr>
            <tr >
                <td width="20%" class="bg-color">New shares </td>
                <td width="80%" ><?php echo $s['newshares'];?></td>
            </tr>
            <tr >
                <td width="20%" class="bg-color">Total shares </td>
                <td width="80%" ><?php echo $s['totalshares'];?></td>
            </tr>
            </tbody>
        </table>
        <br>
        <?php
                                }  
                                ?>
            
            
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
    </body>


</html>