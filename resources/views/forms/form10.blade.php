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
		
		.p1{
			writing-mode: vertical-rl;
			text-orientation: mixed;
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
            font-family: 'SegoeUI', sans-serif;

        }
        table{
            page-break-inside: avoid;
            table-layout:fixed;
        }
    </style>
    </head>
    <body>
    <section class="form-body">
       
        <table width="100%" style="border:0; padding:0;">
            <tr>
                <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 10<br><br></b></span><p style="font-size: 13px; ">Notice of </p><p style="font-size:16px;"><b>CERTIFICATE OF CHARGE CREATED BY THE COMPANY</b></p></td>
                <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 102(1))</td>
                <td width="20%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>
           
            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">Section 102(1) of the Companies Act No. 7 of 2007</td>
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
       
        <br>
        <table style="--primary-text-color: #212121; " width="100%">
            <tbody>
                <tr>
                    <td width="26%"  class="bg-color" >Name of <br>Company</td>
                    <td width="74%"><?php echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?></td>
                </tr>
                <tr>
                    <td width="26%" class="bg-color" ><br>Date and description of <br> Instrument creating or <br> evidencing the Charge </td>
                    <td width="74%">

                        
                        <p><strong>Date of Instrument:</strong> <?php echo $charge_record->charge_date; ?></p>
                        <hr/>

                      <?php
                        $amount_secured = 0;
                        if(count($deedItems)){
                            foreach($deedItems as $d ) {
                                $amount_secured =  $amount_secured + floatval($d['amount_secured']);

                                $is_non_notorial = $charge_record->charge_type == 'Non notarial executed';
                                $deed_or_aggrement = ($is_non_notorial) ? 'Agreement' : 'Deed';
                                ?>
                                 <div class="deeditem">

                                    <p><strong><?php echo $deed_or_aggrement; ?> number:</strong> <?php echo $d['deed_no']; ?></p>
                                    <p><strong>Date of <?php echo $deed_or_aggrement; ?>:</strong> <?php echo $d['deed_date']; ?></p>
                                    <p>
                                        <strong>Amount Secured:</strong>
                                        <?php echo $d['amount_secured']; ?>
                                    </p>
                                    <?php if(!$is_non_notorial): ?>
                                    <p><strong>Lawyers:</strong> <?php echo $d['lawyers']; ?></p>
                                    <?php endif; ?>
                                    
                                    <p><strong>Description:</strong> <?php echo $d['description']; ?></p>
                                 </div>
                                 <br/>
                                 
                                <?php 
                            }
                        }

                      ?>
                    </td>
                </tr>
				<tr>
                    <td width="26%" class="bg-color" ><br>Amount Secured</td>
                    <td width="74%"><?php
                     echo  number_format((float)$amount_secured, 2, '.', '');
                    ?></td>
                </tr>
				<tr>
                    <td width="26%" class="bg-color" ><br>Short particulars of<br>property charged</td>
                    <td width="74%">
                    <?php 
                        echo $charge_record->short_perticular_description; 
                    ?>
                    </td>
                </tr>
				<tr>
                    <td width="26%" class="bg-color" ><br>Name, address and <br> description of the persons <br> entitled to the charge</td>
                    <td width="74%">
                    <?php 
                       if(count($entitledPersons)){
                        foreach($entitledPersons as $d ) {

                            ?>
                             <div class="deeditem">

                                <p><strong>Name:</strong> <?php echo $d['name']; ?></p>
                                <p><strong>Addres:</strong> <?php echo $d['address_1']; ?>,<?php echo $d['address_2']; ?><?php echo ','.$d['address_3']; ?></p>
                                <p><strong>Description:</strong> <?php echo $d['description']; ?></p>
                             </div>
                             <br/>
                             
                            <?php 
                        }
                    }


                    ?>

                    </td>
                </tr>
				<tr>
                    <td width="26%" class="bg-color" ><br>Other details (to comply <br> with Section 102)</td>
                    <td width="74%"><?php  echo $charge_record->other_details; ?></td>
                </tr>	
			</tbody>
        </table>
        <br>

        <table style="border: 0;" width="100%">
            <tbody>
                <tr>
                    <td style="border: 0; ">I/we certify that the particulars given above are correct to the best of my knowledge</td>
                </tr>
            </tbody>
        </table>
		
		<table width="100%">
            <tbody>
                <tr>
                    <td width="25%" height="50" class="bg-color">Signature </td>
                    <td width="75%" height="50">&nbsp;</td>
                </tr>
                <tr height="10px">
                    <td width="25%" class="bg-color">Full Name of Director/Secretary<br>or Other</td>
                    <td width="75%"><?php  echo $charge_record->signing_party_name; ?></td>
                </tr>
                <tr height="10px">
                    <td width="25%" height="35" class="bg-color">State whether Director/Secretary<br>or Other</td>
                    <td width="75%" height="35"><?php echo  ($charge_record->signing_party_state == 'Other') ? $charge_record->signing_party_state_other : $charge_record->signing_party_state; ?></td>
                </tr>
             </tbody>
        </table>
		
        <br>
        
        <?php
            $date_of = $charge_record->date_of ? strtotime($charge_record->date_of) : null;
            if($date_of) {
                $d = date('d', $date_of);
                $m = date('m', $date_of);
                $y = date('Y', $date_of);
            } else {
                $d = $m = $y = null;
            }
            

        ?>
		
		<table  width="100%" height="50.4" style="border:0">
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
		
		<br><br><br><br>
        

        <table style="--primary-text-color: #212121; border:0;" width="100%" autosize="1">
                <tbody>

                <tr>
                    <td style="border:0;"><b>Presented by:</b></td>
                </tr>
                
                

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
		
		<table style="border-color: #FFFFFF;" width="100%" height="30">
            <tbody>
                <tr>
                    <td style="border:0;" colspan="2">Note:</td>
                   
                </tr>
				<tr>
                    <td style="border:0;" width="6%">(1) </td>
					<td style="border:0;">A copy of the instrument by which the charge is created or evidenced (certified as a true copy by a Director or Secretary the Company or an Attorney- at-Law) should be annexed hereto.</td>
                </tr>
				<tr>
					<td style="border:0;" width="6%">(2) </td>
                    <td style="border:0;">“Charge” includes a mortgage – Section 102(13)</td>
                </tr>
				<tr>
					<td style="border:0;" width="6%">(3) </td>
                    <td style="border:0;">In the case of instruments executed in Sri Lanka, to be registered within twenty one (21) working days of the date of the instrument. 
					However in the case of an instrument executed outside Sri Lanka, to be registered within three (3) months of the date of execution of this instrument.</td>
                </tr>
				
				
            </tbody>
        </table>
		
	</body>
</html>	
		
		
	