<!DOCTYPE html>
    <head>
        <meta charset="UTF-8">
        <style>
        table,
        th,
        td {
            border: #212121 solid 1px;
            border-collapse: collapse;
            margin-left: 0px;
            margin-left: 0px;
            margin-bottom: 0px;
            font-size: 12px;
            padding: 5px;
            font-family: 'SegoeUI', sans-serif;
        }

       /* table{
            page-break-inside: avoid;
            table-layout:fixed;
        }*/
			
        font {
            margin-left: 0px;
            margin-left: 0px;
            font-size: 12px;
            font-family: 'SegoeUI', sans-serif;
            margin-bottom: 1px;
        }

        .bg-color {
            background: #D3D3D3;
        }

        .bg-color2{
            background:#e5e5e5;
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
            font-family: 'SegoeUI', sans-serif;

        }
		
         </style>
    </head>

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
    
    
    
    <body>
        <section class="form-body">
        
            <table width="100%" style="border:0; padding:0;" autosize="1">
                <tr>
                    <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                    <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 11<br><br></b></span><p style="font-size:16px;"><b>REGISTER OF CHARGES, AND OF MEMORANDUM OF SATISFACTION OF </b></p>
                    <p style="font-size:16px;"><b>CHARGES OF _____________________________________________________CO., LTD</b></p></td>
                    <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 105(1))</td>
                    <td width="20%" style="border:0; padding:0px;"><img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
                </tr>
                
                <tr>
                    <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">Section 105(1) of the Companies Act No. 7 of 2007</td>
                </tr>
            </table>
            <br>

            <table style="border: 0;"  width="100%" autosize="1" >
                <tbody>
                    <tr>
                        <td width="25%" height="35" style="border: 0;">No. of Company</td>
                        <td width="7%" height="35" style="text-align:center;font-weight:bold;font-size:20px"><?php echo $pv_first; ?></td>
                        <td width="7%" height="35" style="text-align:center;font-weight:bold;font-size:20px"><?php echo $pv_second; ?></td>
                        <td width="61%" height="35" style="font-weight:bold;font-size:20px">&nbsp;<?php echo $pv_number_part; ?></td>
                    </tr>
                </tbody>
            </table>
            
            <br>
            
            <table style="--primary-text-color: #212121; " width="100%" autosize="1">
                <tbody>
                    <tr>
                        <td width="26%" height="50" class="bg-color" >Name of Company</td>
                        <td width="74%"><?php echo ($company_info->name) ? $company_info->name.' '.$postfix : '' ?></td>
                        
                    </tr>
                </tbody>	
            </table>
		
	    <br>

            <table style="border:0" width="100%" autosize="1">

                <tr>
                    <td colspan="2" align="left" class="bg-color2">Date of Registration</td>
                    <td><?php echo $RegisterOfChargesRecord['date_of_registration'];?></td>
                    <td colspan="3" rowspan="7" class="bg-color" align="center">BLANK AREA</td>
                    
                    
                </tr>

                <tr>
                    <td colspan="2" align="left" class="bg-color2">Serial No. of Document in<br> file</td>
                    <td><?php echo $RegisterOfChargesRecord['document_serial_no'];?></td>
                    
                    
                </tr>

                <tr>
                    <td colspan="2" align="left" class="bg-color2">Date of creation of each<br> Charge and description<br> thereof</td>
                    <td><?php echo $RegisterOfChargesRecord['date_of_creation_of_charge'];?></td>
                   
                    
                    
                </tr>

                <tr>
                    <td colspan="2" align="left" class="bg-color2">Date of the acquisition of<br> the Property</td>
                    <td><?php echo $RegisterOfChargesRecord['date_of_acquisition_of_property'];?></td>
 
                    
                   
                </tr>

                <tr>
                    <td colspan="2" align="left" class="bg-color2">Amount secured by the<br> Charge</td>
                    <td><?php echo $RegisterOfChargesRecord['amount_secured_by_charge'];?></td>
                  
                    
                  
                </tr>

                <tr>
                    <td colspan="2" align="left" class="bg-color2">Short particulars of the<br> property or charged</td>
                    <td><?php echo $RegisterOfChargesRecord['short_particulars_of_charge'];?></td>
                    
                    
                   
                </tr>

                <tr>
                    <td colspan="2" align="left" class="bg-color2">Name of the Persons<br> entitled to the Charge</td>
                    <td><?php echo $RegisterOfChargesRecord['person_name_entitled'];?></td>
                 
                    
                    
                </tr>

                <?php if(count($register_charges)) { 
                    foreach($register_charges as $rec) { ?>

                    <tr>
                    <td rowspan="7" width="5%" style="text-rotate: 90;" align="center" class="bg-color2">Particulars relating to the Issue of Debentures of a series</td>
                    <td class="bg-color2">Total amount<br> secured by a series <br>of Debentures</td>
                    <td><?php echo $rec['total_amount_secured'];?></td>
                    <td colspan="2" class="bg-color2">Memorandums of <br>satisfaction Amount</td>
                    <td><?php echo $rec['satisfaction_amount'];?></td>
                    
                   
                </tr>

                <tr>
                    <td class="bg-color2">Date of each Issue <br>of the series</td>
                    <td><?php echo $rec['date_of_issue_series'];?></td>
                    <td colspan="2" class="bg-color2">Amount or rate per<br>cent, of the<br>Commission Allowance<br>or Discount</td>
                    <td><?php echo $rec['amount_commisison_allowance'];?></td>
                    
                   
                </tr>

                <tr>
                    <td class="bg-color2">Amount of each<br> Issue of the series</td>
                    <td><?php echo $rec['amount_issue_series'];?></td>
                    <td colspan="3" class="bg-color" align="center" font-color="white">BLANK AREA</td>
                   
                   
                </tr>

                <tr>
                    <td class="bg-color2" width="20%">Dates of the <br>Resolutions<br> authorizing the<br> Issue of the series</td>
                    <td width="25%"><?php echo $rec['date_of_resolutions'];?></td>
                    <td class="bg-color2" rowspan="3" width="5%" style="text-rotate: 90;" align="center" >Receiver or Manager</td>
                    <td class="bg-color2" width="20%">Date of Ceasing<br>to act</td>
                    <td width="25%"><?php echo $rec['manager_date_of_ceasing'];?></td>
                    
                    
                </tr>

                <tr>
                    <td class="bg-color2">Date of the<br> Covering Deed</td>
                    <td><?php echo $rec['date_of_deed'];?></td>
                    <td rowspan="2" class="bg-color2">Name and date<br>of Appointment</td>
                    <td>
                        <?php echo $rec['manager_name'];?>
                        
                    </td>
                    
                </tr>

                <tr>
                    <td class="bg-color2">General description<br>of the Property <br>Charged</td>
                    <td><?php echo $rec['description_of_property'];?></td>
                    <td><?php echo $rec['manager_date_of_appointment'];?></td>
                   
                </tr>

                <tr>
                    <td class="bg-color2">Names of the<br> Trustees for the<br> Debenture Holders</td>
                    <td><?php echo $rec['name_of_trustee'];?></td>
                    <td colspan="2" class="bg-color2">Signature of<br>Authorised Person</td>
                    <td></td>                  
                </tr> 

                <!-- end row -->
                        
                        
                 <?php  }
                 } else { ?>

                <!-- start row -->

                <tr>
                    <td rowspan="7" width="5%" style="text-rotate: 90;" align="center" class="bg-color2">Particulars relating to the Issue of Debentures of a series</td>
                    <td class="bg-color2">Total amount<br> secured by a series <br>of Debentures</td>
                    <td></td>
                    <td colspan="2" class="bg-color2">Memorandums of <br>satisfaction Amount</td>
                    <td></td>
                    
                   
                </tr>

                <tr>
                    <td class="bg-color2">Date of each Issue <br>of the series</td>
                    <td></td>
                    <td colspan="2" class="bg-color2">Amount or rate per<br>cent, of the<br>Commission Allowance<br>or Discount</td>
                    <td></td>
                    
                   
                </tr>

                <tr>
                    <td class="bg-color2">Amount of each<br> Issue of the series</td>
                    <td></td>
                    <td colspan="3" class="bg-color" align="center" font-color="white">BLANK AREA</td>
                   
                   
                </tr>

                <tr>
                    <td class="bg-color2" width="20%">Dates of the <br>Resolutions<br> authorizing the<br> Issue of the series</td>
                    <td width="25%"></td>
                    <td class="bg-color2" rowspan="3" width="5%" style="text-rotate: 90;" align="center" >Receiver or Manager</td>
                    <td class="bg-color2" width="20%">Date of Ceasing<br>to act</td>
                    <td width="25%"></td>
                    
                    
                </tr>

                <tr>
                    <td class="bg-color2">Date of the<br> Covering Deed</td>
                    <td></td>
                    <td rowspan="2" class="bg-color2">Name and date<br>of Appointment</td>
                    <td></td>
                    
                </tr>

                <tr>
                    <td class="bg-color2">General description<br>of the Property <br>Charged</td>
                    <td></td>
                    <td></td>
                   
                </tr>

                <tr>
                    <td class="bg-color2">Names of the<br> Trustees for the<br> Debenture Holders</td>
                    <td></td>
                    <td colspan="2" class="bg-color2">Signature of<br>Authorised Person</td>
                    <td></td>                  
                </tr> 

                <!-- end row -->

                 <?php } ?>
             

            </table>

            <table style="border: 0;" width="100%" >
            <tbody>
                <tr ><td style="border: 0;" > Note: “Charge” includes a Mortgage – Section 102(13)</td></tr>
		    <tbody>
                
		    </table>

        </section>
    </body>
</html>