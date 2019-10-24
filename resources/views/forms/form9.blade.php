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

        table{
            page-break-inside: avoid;
            table-layout:fixed;
        }

        font {
            margin-left: 0px;
            margin-right: 0px;
            font-size: 14px;
            font-family: 'sans-serif';
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
    
        
        <table width="100%" style="border:0; padding:0;" autosize="1">
            <tr>
                <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 9<br><br></b></span><p style="font-size: 13px; ">Notice of </p><p style="font-size:16px;"><b>ACQUISITION OR REDEMPTION BY COMPANY OF OWN SHARES </b></p></td>
                <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 63(4))</td>
                <td width="20%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>
			
            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">Section 63(4) of the Companies Act No.
                    7 of 2007</td>
            </tr>
        </table>

        <br>

        <table style="border: 0;" width="100%" >
                <tbody>
                    <tr>
                        <td width="28%" style="border: 0; padding:0" >Number of the company </td>
                        <td width="72%" >&nbsp;<?php echo $certificate_no;?></td>
                    </tr>
                </tbody>
            </table>
            <br>
      
            <table style="border: 0;" width="100%" >
                <tbody>
                    <tr>
                        <td width="28%" height="50" class="bg-color">Name of the company </td>
                        <td width="72%" height="50">&nbsp; <?php echo $company_info->name; ?>&nbsp;<?php echo $postfix; ?></td>
                    </tr>
                </tbody>
            </table>
        <br>
        

        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td style="border: 0;" align="justify">Set out in the table below are particulars of the acquisition or Redemption by the above named company of its own shares.</td>
                    
                </tr>
            </tbody>
        </table>

        <br>

		<table style="--primary-text-color: #212121; border: 0" width="100%" autosize="1">
				<tbody>
					<tr>
						<td width="173pt"  class="bg-color" align="center">Name of Person(s) from Whom <br>Shares Acquired or Redeemed &nbsp;</td>
						<td width="102pt"  class="bg-color" align="center">Number of Shares<br>Acquired or<br>Redeemed &nbsp;</td>
						<td width="117pt"  class="bg-color" align="center">Date of Acquisition or<br>Redemption &nbsp;</td>
						<td width="117pt"  class="bg-color" align="center">Class of share acquired or<br>Redeemed &nbsp;</td>
						
                    </tr>
                    
                    <?php 

                    
                    $total_company_shares = isset($callonSharesRecord->total_company_shares) ? floatval($callonSharesRecord->total_company_shares) : 0;
                    $total_acquire_redeem = $total_company_shares;
                    if(count($share_calls)){
                        foreach($share_calls as $call ) {

                            if($call['aquire_or_redeemed'] == 'acquire') {
                                $total_acquire_redeem =  $total_acquire_redeem + floatval($call['aquire_or_redeemed_value']);
                            }else {
                                $total_acquire_redeem = $total_acquire_redeem - floatval($call['aquire_or_redeemed_value']);
                            }

                            if($call['share_class'] == 'PREFERENCE_SHARE') {
                                $share_class = 'Preference Share';
                            }else if($call['share_class'] == 'ORDINERY_SHARE') {
                                $share_class = 'Ordinary Share';
                            }else{
                                $share_class ='';
                            }
                        
                           
                           // $share_class = ucwords(strtolower(str_replace('_',' ',$call['share_class'])));
                            ?>

                            <tr>
                                <td width="173pt" height="75"><?php echo $call['person_name'];?></td>
                                <td width="102pt" height="75"><?php echo $call['aquire_or_redeemed_value'];?> (<?php echo ucwords($call['aquire_or_redeemed']); ?>)</td>
                                <td width="117pt" height="75"><?php echo $call['date'];?></td>
                                <td width="117pt" height="75"><?php echo $share_class;?></td>
                                
                            </tr>

                            <?php 
                        }
                    } else {
                        ?>

                        <tr>
                            <td width="173pt" height="75"></td>
                            <td width="102pt" height="75">></td>
                            <td width="117pt" height="75"></td>
                            <td width="117pt" height="75"></td>
						
					    </tr>


                        <?php 
                    }



                    ?>

					
					<tr>
						<td width="34%" style="border: 0" height="10"></td>
						<td width="20%" style="border: 0" height="10"></td>
						<td width="23%" height="10" class="bg-color">Total number of company <br> Shares after cancellation</td>
                        <td width="23%" height="10" align="right">
                            <?php

                            $balance = ($total_acquire_redeem );
                            $balance = ($balance > 0 ) ? $balance : 0;
                            echo $balance;
                        
                            ?>
                        </td>
					</tr>
					
				</tbody>
			</table>	

			<br>

            <table style="--primary-text-color: #212121; " width="100%" autosize="1">
                <?php 

                    $designation = $callonSharesRecord->signing_party_designation;
                    

                ?>
                <tbody>
                    <tr>
                        <td width="127pt" height="60" class="bg-color" align="left" >Signature of <?php if(count($member)) {echo $member[0]['designation'];} ?> </td>
                        <td width="382pt">&nbsp;</td>
                    </tr>
                    <tr>
                        <td width="127pt" height="50" class="bg-color" align="left">Full Name of <?php if(count($member)) { echo $member[0]['designation'];} ?> </td>
                        <td width="382pt"><?php if(count($member)) { echo $member[0]['first_name'];} ?>&nbsp;<?php if(count($member)) { echo $member[0]['last_name'];} ?></td>
                    </tr>
                </tbody>
            </table>

            <br>

            <?php
            $date =  strtotime($callonSharesRecord->date_of);
            if($date) {
                $d = date('d', $date);
                $m = date('m', $date);
                $y = date('Y', $date);
            } else {
                $d = $m = $y = null;
            }
            

            ?>

            <table style="border: 0" width="100%" >
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


            <table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
                <tbody>

                <tr>
                    <td style="border:0;"><b>Presented by:</b></td>
                </tr>
                
                <tr> 
                    <td width="82pt" height="40" class="bg-color">Full Name </td>
                    <td width="229pt" height="40"><?php echo $loginUser->first_name; ?> <?php echo $loginUser->last_name; ?></td>
                    {{-- <td width="30pt"  height="40" rowspan="6" style="text-rotate: 90;" align="center" class="bg-color">sfvsfv</td>
                    <td width="168pt" height="40" rowspan="6" ></td> --}}
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
                    <td width="229pt">
                    <?php  echo $loginUserAddress->address1.',<br/>'; ?>
                            <?php  echo ( $loginUserAddress->address2 ) ? $loginUserAddress->address2.',<br/>' : ''; ?>
                            <?php  echo $loginUserAddress->city.',<br/>'; ?>
                            <?php  echo '<strong>postcode: </strong>'.$loginUserAddress->postcode; ?>
                    </td>
                </tr>
                </tbody>
            </table>





    </body>

</html>