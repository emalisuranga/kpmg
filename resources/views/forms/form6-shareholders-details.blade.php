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

            <?php 
              if(isset($shareholdersList[0]['id'] )) { ?>

            <span>current issue of shares - shareholders individual details</span>

              <?php } ?>


            <?php 

                if(isset($shareholdersList[0]['id'] )) {
                foreach($shareholdersList as $s ){

                    if($s['status'] == 1 ) {
                        $current_shares = $s['shareRow']['no_of_shares'];
                        $new_shares = ($s['shareRow']['new_shares']) ? $s['shareRow']['new_shares'] - $s['shareRow']['no_of_shares'] : 0;
                        $total_shares = $s['shareRow']['new_shares'];
                    } else {
                        $current_shares = ( isset($s['shareRow']['new_shares'])) ?  $s['shareRow']['new_shares'] : 0;
                        $new_shares =  ( isset($s['shareRow']['new_shares'])) ?  $s['shareRow']['new_shares'] : 0;
                        $total_shares = ( isset($s['shareRow']['new_shares'])) ?  $s['shareRow']['new_shares'] : 0;
                    }

                    ?>

            <table  width="100%" autosize="1">
                <tbody>
                <tr>
                    <td width="20%"  height="40" class="bg-color">Full Name </td>
                    <td width="80%"  height="40"><?php
                
                                echo $s['firstname'] . ' ' . $s['lastname'];
                
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
                    <td width="80%"><?php echo $current_shares;?></td>
                </tr>
                <tr >
                    <td width="20%" class="bg-color">New shares </td>
                    <td width="80%" ><?php echo $new_shares;?></td>
                </tr>
                <tr >
                    <td width="20%" class="bg-color">Total shares </td>
                    <td width="80%" ><?php echo $total_shares;?></td>
                </tr>

                <tr >
                    <td width="20%" class="bg-color">Share Type </td>
                    <td width="80%">

                    <?php

                    if($s['shareRow']['type'] == 'core_share') {
                        echo 'Core share (Group: ' . $s['shareRow']['name'] . ')';
                    } else {
                        echo 'Single share';
                    }


                    ?>
                    </td>
                </tr>
                </tbody>
            </table>
            <br>
            <?php
                        }  

                    }
                        ?>

            
        <br>

        <?php 
         if(isset($shareholderFirmList[0]['id'] ) ){ ?>

        <span>current issue of shares - shareholders firm details</span>

         <?php  } ?>

        <?php 
          if(isset($shareholderFirmList[0]['id'] ) ){
            foreach($shareholderFirmList as $s ){

                if($s['status'] == 1 ) {
                    $current_shares = $s['shareRow']['no_of_shares'];
                    $new_shares = $s['shareRow']['new_shares'] - $s['shareRow']['no_of_shares'];
                    $total_shares = $s['shareRow']['new_shares'];
                } else {
                    $current_shares =$s['shareRow']['new_shares'];
                    $new_shares = $s['shareRow']['new_shares'];
                    $total_shares =$s['shareRow']['new_shares'];
                }
                ?>

        <table  width="100%" autosize="1">
            <tbody>
            <tr>
                <td width="20%"  height="40" class="bg-color">Company Name </td>
                <td width="80%"  height="40"><?php echo $s['firm_name'];?></td>
            </tr>

            <tr>
                <td width="20%"  height="40" class="bg-color">Registration no </td>
                <td width="80%"  height="40"><?php echo $s['pvNumber'];?></td>
            </tr>
            
            <tr >
                <td width="20%" class="bg-color">Current shares </td>
                <td width="80%"><?php echo $current_shares;?></td>
            </tr>
            <tr >
                <td width="20%" class="bg-color">New shares </td>
                <td width="80%" ><?php echo $new_shares;?></td>
            </tr>
            <tr >
                <td width="20%" class="bg-color">Total shares </td>
                <td width="80%" ><?php echo $total_shares;?></td>
            </tr>
            </tbody>
        </table>
        <br>
        <?php
                                } 
                                
                            }
                                ?>
            
            
            <br>
            <?php
          //  $date_of = $callonSharesRecord->date_of ? strtotime($callonSharesRecord->date_of) : null;
            $date_of = time();
            if($date_of) {
                $d = date('d', $date_of);
                $m = date('m', $date_of);
                $y = date('Y', $date_of);
            } else {
                $d = $m = $y = null;
            }
            

        ?>
        <table  width="100%" height="50.4" style="border:0" autosize="1">
            <tbody>
                <tr>
                    <td height="30" align="right" style="border:0">Date:</td>
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
                    <td width="30%" height="22"  style="border:0"></td>
                    <td width="10%" style="border:0"> </td>
                    <td colspan="2" class="bg-color">
                        <center>Day</center>
                    </td>
                    <td width="10%" style="border:0"></td>
                    <td colspan="2" class="bg-color">
                        <center>Month</center>
                    </td>
                    <td width="10%" style="border:0"></td>
                    <td colspan="4" class="bg-color" >
                        <center>Year</center>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
    </body>


</html>