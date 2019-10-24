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
                table-layout:fixed;
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
                    <td width="10%" style="border:0; padding:0px;" rowspan="2"><img width="100" height="100" src="{{ URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                    <td width="67%" style="border:0; font-size: 20px; padding-top:20px; padding-left:105px " align="center"><b>FORM 20<br></b></td>
                    <td width="13%" style="border:0; padding:0px; font-size: 12px;" align="left">(Section 223(2))</td>
                    <td width="10%" style="border:0; padding:0px;" rowspan="2"> <img width="130" height="auto" src="{{ URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
                </tr>
                <tr>0p-`
                    <td width="69%" style="border:0; font-size: 12px; padding-top:5px; padding-left:5px " align="center" colspan="2">Notice of</td>               
                </tr>
                <tr>
                    <td colspan="4" align="center" style="border:0; font-size:15px; padding:0;"><b>CHANGE OF DIRECTOR/SECRETARY AND<br>
                                            PARTICULARS OF DIRECTOR/SECRETARY</b></td>
                </tr>
                <tr>
                    <td colspan="4"  style="border:0; padding:0px; font-size:13px; padding-left:230px;" colspan="4">Section 223(2)) of the Companies Act No. 7 of 2007</td>
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
                    <td width="72%" height="50">&nbsp; <?php echo $comname; ?>&nbsp;<?php echo $postfix; ?></td>
                </tr>
            </tbody>
        </table>
            <br>

           <!-- <span style="font-size: 14px;">Indicate the purpose for which this notice is given by placing <span style="font-size:16px;"> &#10003; </span>  in the appropriate box </span>-->

             <table width="100%" autosize="1">
                <tbody>
                <tr>
                    <td colspan="2" style="border-bottom; border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 14px;"> <strong><font ><b>Indicate the purpose for which this notice is given by placing <span style="font-size:16px;"> &#10003; </span>  in the appropriate box</b></font></strong></td>
                </tr>
                    <tr>
                        <td width="90%" height="50" class="bg-color">(a) &nbsp; &nbsp; &nbsp; Change of Director and/or particulars of Directors.</td>
                        <td width="10%" height="50"  style="text-align:center;font-size:20px;font-weight:bold"><?php echo ($directorChanged) ? '&#x02713;' : '' ; ?></td>
                    </tr>
                    <tr>
                        <td width="90%" height="50" class="bg-color">(b)  &nbsp; &nbsp; &nbsp; Change of secretary and/or particulars of secretary</td>
                        <td width="10%" height="50" style="text-align:center;font-size:20px;font-weight:bold"><?php echo ($secChagned) ? '&#x02713;' : '' ; ?></td>
                    </tr>
                </tbody>
            </table>
            <br>

           <!-- <span style="font-size: 14px;"> Directors/Secretary ceasing to hold office</span>-->

            <table width="100%" autosize="1">
                <tr>
                    <td colspan="5" style="border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 17px;"> <strong><b> Directors/Secretary ceasing to hold office</b></strong></td>
                </tr>
                <tr>
                    <td width="25%" align="center" class="bg-color">Full name</td>
                    <td width="15%" align="center" class="bg-color">Office: Director/ Secretary</td>
                    <td width="25%" align="center" class="bg-color">Residential address</td>
                    <td width="15%" align="center" class="bg-color">Date on which he ceased to hold office</td>
                    <td width="20%" align="center" class="bg-color">Reason</td>
                </tr>
                <?php 
                $membersIds = array();
         foreach($removedMembers as $r ){ 

            if(in_array($r['id'],$membersIds)){
                continue;
            }else{
                $membersIds[] = $r['id'];
            }

                                        ?>
                <tr>
                    <td width="25%" height="50">&nbsp;<?php echo $r['first_name']; echo " "; echo $r['last_name'];?></td>
                    <td width="15%" height="50">&nbsp;<?php echo $r['value'];?></td>
                    <td width="25%" height="50">&nbsp;<?php echo $r['address1']; echo ","; echo $r['address2']; echo ","; echo $r['city']; echo ".";?></td>
                    <td width="15%" height="50">&nbsp;<?php echo $r['ceased_date'];?></td>
                    <td width="20%" height="50">&nbsp;<?php echo $r['ceased_reason'];?></td>
                </tr>
                <?php  }  
                            ?>   
                             <?php 
                              $memberFirmsIds = array();
         foreach($removedFirms as $rf ){ 
            if(in_array($rf['id'],$memberFirmsIds)){
                continue;
            }else{
                $memberFirmsIds[] = $rf['id'];
            }
                                        ?>
                <tr>
                    <td width="25%" height="50">&nbsp;<?php echo $rf['name'];?></td>
                    <td width="15%" height="50">&nbsp;<?php echo $rf['value'];?></td>
                    <td width="25%" height="50">&nbsp;<?php echo $rf['address1']; echo ","; echo $rf['address2']; echo ","; echo $rf['city']; echo ".";?></td>
                    <td width="15%" height="50">&nbsp;<?php echo $rf['ceased_date'];?></td>
                    <td width="20%" height="50">&nbsp;<?php echo $rf['ceased_reason'];?></td>
                </tr>
                <?php  }  
                            ?> 
            </table>
            <br>

            <!--<span>Appointment of new Directors/Secretaries</span> -->

            <table width="100%" autosize="1">
                <tr>
                    <td colspan="5" style="border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 17px;"> <strong><b>Appointment of new Directors/Secretaries</b></strong></td>
                </tr>
                <tr>
                    <td width="25%" align="center" class="bg-color">Full name</td>
                    <td width="15%" align="center" class="bg-color">Office: Director/ Secretary</td>
                    <td width="25%" align="center" class="bg-color">Residential address</td>
                    <td width="15%" align="center" class="bg-color">Email Address</td>
                    <td width="20%" align="center" class="bg-color">Date of appointment</td>
                </tr>
                <?php 
         foreach($newMembers as $n ){ 
                                        ?>
                <tr>
                    <td width="25%" height="50">&nbsp;<?php echo $n['first_name']; echo " "; echo $n['last_name'];?></td>
                    <td width="15%" height="50">&nbsp;<?php echo $n['value'];?></td>
                    <td width="25%" height="50">&nbsp;<?php echo $n['address1']; echo ","; echo $n['address2']; echo ","; echo $n['city']; echo ".";?></td>
                    <td width="15%" height="50">&nbsp;<?php echo $n['email'];?></td>
                    <td width="20%" height="50">&nbsp;<?php echo $n['date_of_appointment'];?></td>
                </tr>
                <?php  }  
                            ?> 

                <?php 
         foreach($newMemberFirms as $nf ){ 
                                        ?>
                <tr>
                    <td width="25%" height="50">&nbsp;<?php echo $nf['name'];?></td>
                    <td width="15%" height="50">&nbsp;<?php echo $nf['value'];?></td>
                    <td width="25%" height="50">&nbsp;<?php echo $nf['address1']; echo ","; echo $nf['address2']; echo ","; echo $nf['city']; echo ".";?></td>
                    <td width="15%" height="50">&nbsp;<?php echo $nf['email'];?></td>
                    <td width="20%" height="50">&nbsp;<?php echo $nf['date_of_appointment'];?></td>
                </tr>
                <?php  }  
                            ?>             
                            
            </table>
            <br>

           <!-- <span>Presented by</span>

            <table  width="100%">
                <tbody>
                <tr>
                    <td width="20%"  height="40" class="bg-color">Full Name </td>
                    <td width="80%"  height="40"></td>
                </tr>
                <tr>
                    <td width="20%" class="bg-color">Email Address</td>
                    <td width="80%"></td>
                </tr>
                <tr>
                    <td width="20%" class="bg-color">Telephone No. </td>
                    <td width="80%"></td>
                </tr>
                <tr>
                    <td width="20%" class="bg-color">Mobile No. </td>
                    <td width="80%"></td>
                </tr>
                </tbody>
            </table>-->

            <table width="100%" autosize="1">
                <tbody>
                <tr>
                    <td colspan="2" style=" border:1px solid #000;border-top:1px solid #fff; border-right:1px solid #fff; border-left:1px solid #fff; word-break: break-all; font-size: 14px;"> <strong><font ><b>Presented by:</b></font></strong></td>
                </tr>
                <tr>
                    <td style="width:132pt;" height="50" class="bg-color">Full Name </td>
                    <td style="width:380pt;" height="50"><?php echo $ufullname; ?></td>
                </tr>
                <tr height="10px">
                    <td style="width:132pt;" class="bg-color">Email Address</td>
                    <td style="width:380pt;"><?php echo $uemail; ?></td>
                </tr>
                <tr height="10px">
                    <td style="width:132pt;" height="35" class="bg-color">Telephone No.</td>
                    <td style="width:380pt;" height="35"><?php echo $utelephone; ?></td>
                </tr>
                <tr height="10px">
                    <td style="width:132pt;" height="30" class="bg-color">Mobile No.</td>
                    <td style="width:380pt;" height="30"><?php echo $umobile; ?></td>
                </tr>
                </tbody>
            </table>

            <p style="page-break-before:always"></p>

          <div style="text-align: center;"><b>Change of name or residential address of Director/Secretary</b></div>
            <br >
            <br >
    <?php foreach($editedDirectors as $n ){ ?>
            <table  width="100%" style="border-top:0; border-left:0;" >
                <tbody>
                    <tr >
                        <td width="20%" style="border:1">Director</td>
                        <td width="40%" align="center"  class="bg-color">Present</td>
                        <td width="40%" align="center"  class="bg-color">Former</td>
                    </tr>
                    <tr>
                        <td width="20%"  class="bg-color">Title </td>
                        <td width="40%"><?php echo $n['new_title'];?></td>
                        <td width="40%"><?php echo $n['old_title'];?></td>
                    </tr>
                    <tr>
                        <td width="20%"  class="bg-color">First Name </td>
                        <td width="40%"><?php echo $n['new_firstname'];?></td>
                        <td width="40%"><?php echo $n['old_firstname'];?></td>
                    </tr>
                    <tr  >
                        <td width="20%" class="bg-color">Last Name</td>
                        <td width="40%"><?php echo $n['new_lastname'];?></td>
                        <td width="40%"><?php echo $n['old_lastname'];?></td>
                    </tr>
                    <tr >
                        <td width="20%" class="bg-color">Residential Address. </td>
                        <td width="40%"><?php echo $n['new_address'];?></td>
                        <td width="40%"><?php echo $n['old_address'];?></td>
                    </tr>
                    
                    
                    {{-- <tr >
                        <td width="20%" class="bg-color">Mobile No. </td>
                        <td width="40%"></td>
                        <td width="40%"></td>
                    </tr> --}}
                </tbody>
            </table>
            <br >

            <table  width="100%" height="50.4" style="border:0">
                <tbody>
                    <tr>
                        <td height="30" align="right" style="border:0">Date of change:</td>
                        <td width="8%" style="border:0"    ></td>
                        <td width="5%"><?php echo date('d', strtotime($n['date']))[0];?></td>
                        <td width="5%"><?php echo date('d', strtotime($n['date']))[1];?></td>
                        <td width="10%"  style="border:0"   ></td>
                        <td width="5%"><?php echo date('m', strtotime($n['date']))[0];?></td>
                        <td width="5%"><?php echo date('m', strtotime($n['date']))[1];?></td>
                        <td width="10%" style="border:0"    ></td>
                        <td width="5%"><?php echo date('Y', strtotime($n['date']))[0];?></td>
                        <td width="5%"><?php echo date('Y', strtotime($n['date']))[1];?></td>
                        <td width="5%"><?php echo date('Y', strtotime($n['date']))[2];?></td>
                        <td width="5%"><?php echo date('Y', strtotime($n['date']))[3];?></td>
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
        <br >
    <?php  }               ?>

    <?php foreach($editedSecs as $n ){ ?>
        <table  width="100%" style="border-top:0; border-left:0;" >
            <tbody>
                <tr >
                    <td width="20%" style="border:1">Secretary</td>
                    <td width="40%" align="center"  class="bg-color">Present</td>
                    <td width="40%" align="center"  class="bg-color">Former</td>
                </tr>
                <tr>
                    <td width="20%"  class="bg-color">Title </td>
                    <td width="40%"><?php echo $n['new_title'];?></td>
                    <td width="40%"><?php echo $n['old_title'];?></td>
                </tr>
                <tr>
                    <td width="20%"  class="bg-color">First Name </td>
                    <td width="40%"><?php echo $n['new_firstname'];?></td>
                    <td width="40%"><?php echo $n['old_firstname'];?></td>
                </tr>
                <tr  >
                    <td width="20%" class="bg-color">Last Name</td>
                    <td width="40%"><?php echo $n['new_lastname'];?></td>
                    <td width="40%"><?php echo $n['old_lastname'];?></td>
                </tr>
                <tr >
                    <td width="20%" class="bg-color">Residential Address. </td>
                    <td width="40%"><?php echo $n['new_address'];?></td>
                    <td width="40%"><?php echo $n['old_address'];?></td>
                </tr>
                
                
                {{-- <tr >
                    <td width="20%" class="bg-color">Mobile No. </td>
                    <td width="40%"></td>
                    <td width="40%"></td>
                </tr> --}}
            </tbody>
        </table>
        <br >

        <table  width="100%" height="50.4" style="border:0">
            <tbody>
                <tr>
                    <td height="30" align="right" style="border:0">Date of change:</td>
                    <td width="8%" style="border:0"    ></td>
                    <td width="5%"><?php echo date('d', strtotime($n['date']))[0];?></td>
                    <td width="5%"><?php echo date('d', strtotime($n['date']))[1];?></td>
                    <td width="10%"  style="border:0"   ></td>
                    <td width="5%"><?php echo date('m', strtotime($n['date']))[0];?></td>
                    <td width="5%"><?php echo date('m', strtotime($n['date']))[1];?></td>
                    <td width="10%" style="border:0"    ></td>
                    <td width="5%"><?php echo date('Y', strtotime($n['date']))[0];?></td>
                    <td width="5%"><?php echo date('Y', strtotime($n['date']))[1];?></td>
                    <td width="5%"><?php echo date('Y', strtotime($n['date']))[2];?></td>
                    <td width="5%"><?php echo date('Y', strtotime($n['date']))[3];?></td>
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
    <br >
<?php  }               ?>

<?php foreach($editedSecfirms as $n ){ ?>
    <table  width="100%" style="border-top:0; border-left:0;" >
        <tbody>
            <tr >
                <td width="20%" style="border:1">Secretary Firm</td>
                <td width="40%" align="center"  class="bg-color">Present</td>
                <td width="40%" align="center"  class="bg-color">Former</td>
            </tr>

            <tr>
                <td width="20%"  class="bg-color">First Name </td>
                <td width="40%"><?php echo $n['new_firstname'];?></td>
                <td width="40%"><?php echo $n['old_firstname'];?></td>
            </tr>
            <tr  >
                <td width="20%" class="bg-color">Last Name</td>
                <td width="40%"><?php echo $n['new_lastname'];?></td>
                <td width="40%"><?php echo $n['old_lastname'];?></td>
            </tr>
            <tr >
                <td width="20%" class="bg-color">Residential Address. </td>
                <td width="40%"><?php echo $n['new_address'];?></td>
                <td width="40%"><?php echo $n['old_address'];?></td>
            </tr>
            
            
            {{-- <tr >
                <td width="20%" class="bg-color">Mobile No. </td>
                <td width="40%"></td>
                <td width="40%"></td>
            </tr> --}}
        </tbody>
    </table>
    <br >

    <table  width="100%" height="50.4" style="border:0">
        <tbody>
            <tr>
                <td height="30" align="right" style="border:0">Date of change:</td>
                <td width="8%" style="border:0"    ></td>
                <td width="5%"><?php echo date('d', strtotime($n['date']))[0];?></td>
                <td width="5%"><?php echo date('d', strtotime($n['date']))[1];?></td>
                <td width="10%"  style="border:0"   ></td>
                <td width="5%"><?php echo date('m', strtotime($n['date']))[0];?></td>
                <td width="5%"><?php echo date('m', strtotime($n['date']))[1];?></td>
                <td width="10%" style="border:0"    ></td>
                <td width="5%"><?php echo date('Y', strtotime($n['date']))[0];?></td>
                <td width="5%"><?php echo date('Y', strtotime($n['date']))[1];?></td>
                <td width="5%"><?php echo date('Y', strtotime($n['date']))[2];?></td>
                <td width="5%"><?php echo date('Y', strtotime($n['date']))[3];?></td>
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
<br >
<?php  }               ?>
<br>

        <span ><b>Names and residential address of the every person who is a Director/Secretary of the company from the date of this notice </b></span>
        <br>
        <br>
        <table width="100%" >
            <tr>
                <td class="bg-color">Full Name</td>
                <td class="bg-color">Designation</td>
                <td class="bg-color">Residential Address</td>
            </tr>
        <?php foreach($activemembers as $n ){ ?>
            <?php if($n['designation_type'] == 'Director') : ?>
            <tr>
                <td width="40%" height="40"><?php echo $n['fullname'];?></td>
                <td width="10%" height="40"><?php echo $n['designation_type'];?></td>
                <td width="50%" height="40"><?php echo $n['resaddress'];?></td>
            </tr>
            <?php endif; ?>
        <?php  }               ?>
        
        <?php foreach($newMembers as $n ){ ?>
            <?php if($n['value'] == 'Director') : ?>
            <tr>
                <td width="40%" height="40"><?php echo $n['first_name']; echo " "; echo $n['last_name'];?></td>
                <td width="10%" height="40"><?php echo $n['value'];?></td>
                <td width="50%" height="40"><?php echo $n['address1']; echo ","; echo $n['address2']; echo ","; echo $n['city']; echo ".";?></td>
            </tr>
            <?php endif; ?>
        <?php  }               ?>

        <?php foreach($activemembers as $n ){ ?>
            <?php if($n['designation_type'] == 'Secretary') : ?>
            <tr>
                <td width="40%" height="40"><?php echo $n['fullname'];?></td>
                <td width="10%" height="40"><?php echo $n['designation_type'];?></td>
                <td width="50%" height="40"><?php echo $n['resaddress'];?></td>
            </tr>
            <?php endif; ?>
        <?php  }               ?>
        
        <?php foreach($newMembers as $n ){ ?>
            <?php if($n['value'] == 'Secretary') : ?>
            <tr>
                <td width="40%" height="40"><?php echo $n['first_name']; echo " "; echo $n['last_name'];?></td>
                <td width="10%" height="40"><?php echo $n['value'];?></td>
                <td width="50%" height="40"><?php echo $n['address1']; echo ","; echo $n['address2']; echo ","; echo $n['city']; echo ".";?></td>
            </tr>
            <?php endif; ?>
        <?php  }               ?>

        <?php foreach($activesecs_firms as $n ){ ?>
            <tr>
                <td width="40%" height="40"><?php echo $n['firm_name'];?></td>
                <td width="10%" height="40"><?php echo 'Secretary Firm';?></td>
                <td width="50%" height="40"><?php echo $n['firm_address'];?></td>
            </tr>
        <?php  }               ?>

        <?php foreach($newMemberFirms as $n ){ ?>
            <tr>
                <td width="40%" height="40"><?php echo $nf['name'];?></td>
                <td width="10%" height="40"><?php echo 'Secretary Firm';?></td>
                <td width="50%" height="40"><?php echo $nf['address1']; echo ","; echo $nf['address2']; echo ","; echo $nf['city']; echo ".";?></td>
            </tr>
        <?php  }               ?>

        </table>
        <br >   

        <table  width="100%" >
            <tbody>
                <tr>
                    <td width="20%"  height="80" class="bg-color">Signature of <?php echo $member[0]['designation']; ?></td>
                    <td width="80%"  height="80"></td>
                </tr>
                <tr  >
                    <td width="20%" height="80" class="bg-color">Full Name of <?php echo $member[0]['designation']; ?></td>
                    <td width="80%" height="80">&nbsp;<?php echo $member[0]['first_name']; ?>&nbsp;<?php echo $member[0]['last_name']; ?></td>
                </tr>
            </tbody>
        </table>
        <br>

         <table  width="100%" height="50.4" style="border:0;" >
            <tbody>
                <tr>
                    <td height="30" align="right" style="border:0">Date:</td>
                    <td width="8%" style="border:0"    ></td>
                    <td width="5%"><?php echo date('d', strtotime($todayDate))[0];?></td>
                    <td width="5%"><?php echo date('d', strtotime($todayDate))[1];?></td>
                    <td width="10%"  style="border:0"   ></td>
                    <td width="5%"><?php echo date('m', strtotime($todayDate))[0];?></td>
                    <td width="5%"><?php echo date('m', strtotime($todayDate))[1];?></td>
                    <td width="10%" style="border:0"    ></td>
                    <td width="5%"><?php echo date('Y', strtotime($todayDate))[0];?></td>
                    <td width="5%"><?php echo date('Y', strtotime($todayDate))[1];?></td>
                    <td width="5%"><?php echo date('Y', strtotime($todayDate))[2];?></td>
                    <td width="5%"><?php echo date('Y', strtotime($todayDate))[3];?></td>
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
        {{-- <br >

        <span >Presented by</span>

        <table  width="100%">
            <tbody>
            <tr>
                <td width="20%"  height="40" class="bg-color">Full Name </td>
                <td width="80%"  height="40"></td>
            </tr>
            <tr  >
                <td width="20%" class="bg-color">Email Address</td>
                <td width="80%"></td>
            </tr>
            <tr >
                <td width="20%" class="bg-color">Telephone No. </td>
                <td width="80%"></td>
            </tr>
            <tr >
                <td width="20%" class="bg-color">Mobile No. </td>
                <td width="80%" ></td>
            </tr>
            </tbody>
        </table> --}}
        <br>
        <span align="center">Notice should be delivered to the Registrar of Companies, within 20 working days of the change occurring</span>

             <htmlpagefooter name="page-footer" >
	<div class="page-no" style="text-align:right" >{PAGENO}</div>
        </htmlpagefooter>
    </body>


</html>