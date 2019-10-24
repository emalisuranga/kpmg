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

        table{
            page-break-inside: avoid;
            table-layout:fixed;
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
            font-family: 'SegoeUI', sans-serif;

        }
    </style>
    </head>
    <body>
    <section class="form-body">
   
        <table width="100%" style="border:0; padding:0;" autosize="1">
            <tr>
                <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 6<br><br></b></span><p style="font-size: 13px; ">Notice of </p><p style="font-size:16px;"><b>ISSUE OF SHARES</b></p></td>
                <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 55(1))</td>
                <td width="20%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>

            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">Section 55(1) of the Companies Act No.
                    7 of 2007</td>
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

 


                <span style="font-size: 14px;">Set out in the table below are particulars of the issue of shares by the above company</span>
           
           <table width="100%">
               <tr>
                   <td width="31%" align="center" class="bg-color">Date of issue</td>
                   <td width="31%" align="center" class="bg-color">Number of Shares</td>
                   <td width="38%" align="center" class="bg-color">Consideration or its value determined as Provided in Section 58(2) </td>
               </tr>

               <?php 

               $total_value = 0;
               
               
               if(isset($share_calls[0]['id'])) {
                   foreach($share_calls as $issue) {

                       if($issue['is_issue_type_as_cash'] == 'yes') {
                           $number_of_shares = floatval($issue['no_of_shares_as_cash']);
                           $consideration = floatval($issue['consideration_of_shares_as_cash']);
                           $total_value+= $consideration;
                       }
                       else  {
                        $number_of_shares = floatval($issue['no_of_shares_as_non_cash']);
                        $consideration = floatval($issue['consideration_of_shares_as_non_cash']);
                        $total_value+= $consideration;
                       }
                       ?>

                       <tr>
                        <td align="center" width="32%" height="30"><?php echo $issue['date_of_issue']; ?></td>
                        <td align="center"  width="32%" height="30"><?php echo $number_of_shares; ?></td>
                        <td align="center"  width="36%" height="30"><?php echo $consideration; ?> </td>
                    </tr>


                       <?php 
                   }
               }
               ?>
               
           </table>
           <br>

           <span style="font-size: 14px;">[Attach particulars of shareholders with their full names, addresses, NIC Nos/Passport Nos* and also a
copy of terms of issue, approved under section 51(2) if any (to be declared in terms of section 51(4(b))]</span>

           <table width="100%" >
               <tbody>
                   <tr>
                       <td width="70%" height="50"  align="right" class="bg-color">Stated Capital prior to this issue </td>
                       <td width="25%" height="50" align="right" >&nbsp; <?php echo $callonSharesRecord->stated_capital; ?></td>
                       <td width="5%" height="50" align="right" class="bg-color">(a)</td>
                   </tr>
                   <tr>
                       <td width="70%" height="50" align="right"  class="bg-color">The consideration for which or its value determined as provided in section 58(2) for which the shares were issued in this issue </td>
                       <td width="25%" height="50" align="right" >&nbsp; <?php echo $total_value; ?></td>
                       <td width="5%" height="50" align="right" class="bg-color" >(b)</td>
                   </tr>
                   <tr>
                       <td width="70%" height="50"  align="right" class="bg-color">Stated Capital following this issue (a+b=c) </td>
                       <td width="25%" height="50" align="right" >&nbsp; <?php echo ($callonSharesRecord->stated_capital + $total_value); ?></td>
                       <td width="5%" height="50" align="right" class="bg-color">(c)</td>
                   </tr>
               </tbody>
           </table>
           <br>

           <table style="--primary-text-color: #212121; " width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="127pt" height="75" class="bg-color" align="left" >
                        Signature of <?php echo $stakeholder_info['type'] ;?> 
                    </td>
                    <td width="382pt">&nbsp;</td>
                </tr>
                <tr>
                    <td width="127pt" height="60" class="bg-color" align="left">Full Name of <?php echo $stakeholder_info['type'] ;?> </td>
                    <td width="382pt"><?php echo $stakeholder_info['name'] ;?> </td>
                </tr>
            </tbody>
        </table>
       <br>

      <?php
           // $date_of = $callonSharesRecord->date_of ? strtotime($callonSharesRecord->date_of) : null;
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
        <br/>

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
<br>
        
    </body>
</html>