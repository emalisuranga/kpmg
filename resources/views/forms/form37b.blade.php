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
      
        <table width="100%" style="border:0; padding:0;" autosize="1">
            <tr>
                <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 37<br><br></b></span><p style="font-size:16px;"><b> NOTICE OF CHANGE OF NAME OF OVERSEAS COMPANY</b></p></td>
                <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 493(4)(b)) </td>
                <td width="20%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>
			
            
            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">In terms of Section  493 (4)(b) of the Companies Act No. 7 of 2007</td>
            </tr>
        </table>
        <br>
		
		<!-- <table width="100%" style="border:0; padding:0px;" autosize="1">
			<tr>
				<td style="border:0; padding:0px;" align="center" ><span style="font-size: 12px;" align= "center"><i> [If there is insufficient space on the form to supply the information required, attach a separate sheet containing the
information set out in the prescribed format. Please note that the information on this form must be either typewritten
or printed. It must not be handwritten]  <i></span></td>
			</tr>
		</table>
<br> -->

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
                    <td width="117.1pt" height="40" class="bg-color">Registered Name of
The Overseas Company </td>
                    <td width="392.7pt" height="40"><?php echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?></td>
                </tr>
            </tbody>
        </table>

        <br>

        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td width="117.1pt" height="40" class="bg-color">New Name of the
Company </td>
                    <td width="392.7pt" height="40"><?php echo $record->new_name; ?></td>
                </tr>
            </tbody>
        </table>

        <br>
        
        <?php
            $date_of = $record->date_of_change ? strtotime($record->date_of_change) : null;
            if($date_of) {
                $d = date('d', $date_of);
                $m = date('m', $date_of);
                $y = date('Y', $date_of);
            } else {
                $d = $m = $y = null;
            }
            

        ?>
		


        <table style="border: 0" width="100%" >
            <tbody>
            <tr>
            <td height="30" align="right" style="border:0">&nbsp;</td>
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
            <td width="24%" height="5" style="border: 0" align="left">Date of
                Change: </td>
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

        
       
       <?php
         
         $auth_persons = str_replace('[', '', $record->auth_person_name);
         $auth_persons = str_replace(']', '', $auth_persons);
         $auth_persons = explode(',', $auth_persons);


         if(is_array($auth_persons) && count($auth_persons)) { ?>
       
        <table style="--primary-text-color: #212121; " width="100%" autosize="1">
            <tbody>
                <?php foreach($auth_persons as $p) { 
                      
                      $p_name = $p == '"Other"' ? $record->other_auth_person : str_replace('"', '', $p);
                    
                    ?>
                    <tr>
                    <td style="border:0; padding-left:10px;"width="127.5pt" height="75">Signature of the Authorised Person:</td>
                    <td width="382.5pt" height="50"> </td>
                </tr>
                <tr>
                <td style="border:0; padding-left:10px;"width="127.5pt" height="60">Name of the Authorised person:</td>
                    <td style="" width="382.5pt" height="30"><?php echo $p_name; ?></td>
                </tr>
                
                <?php } ?>
                
            </tbody>
        </table>
        <br>
       <?php } ?>

        
		


 <?php
            $date_of = $record->date_of ? strtotime($record->date_of) : null;
            if($date_of) {
                $d = date('d', $date_of);
                $m = date('m', $date_of);
                $y = date('Y', $date_of);
            } else {
                $d = $m = $y = null;
            }
            

        ?>
 <table style="border: 0" width="100%" >
            <tbody>
            <tr>
            <td height="30" align="right" style="border:0">&nbsp;</td>
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
            <td width="24%" height="5" style="border: 0" align="left">Date:</td>
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
                    <td width="229pt" height="40"><?php echo $loginUser->first_name; ?> <?php echo $loginUser->last_name; ?></td>
                    {{-- <td width="30pt"  height="40" rowspan="6" style="text-rotate: 90;" align="center" class="bg-color">Signature</td>
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