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
            overflow: hidden;
            table-layout: fixed;
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
        <table width="100%" style="border:0; padding:0;" autosize="1">
                <tr>
                    <td width="10%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                    <td width="58%" style="border:0; font-size: 18px; padding-top:20px; padding-left:140px " align="center"><b>FORM 14<br><br></b>Notice of</td>
                    <td width="14%" style="border:0; padding:0px; font-size: 10px;" align="left">&nbsp;&nbsp;&nbsp;&nbsp;(Section 116(4)),<br>
 124(3)(a) and 124(4</td>
                    <td width="18%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
                </tr>
                <tr>
                    <td colspan="4" align="center" style="border:0; font-size:16px; padding:0;"><b>CHANGE OF LOCATION OF THE RECORDS AND REGISTERS</b></td>
                </tr>
                <tr>
                    <td colspan="4"  style="border:0; padding:0px; font-size:13px; padding-left:145px;">Section 116(4), 124(3)(a) and 124(4) of the Companies Act No. 7 of 2007
</td>
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

                <table width="100%" autosize="1">
                        <tbody>
                            <tr>
                                <td width="28%" height="50" class="bg-color">Address of Company </td>
                                <td width="72%" height="50"><?php echo $comAd->address1; ?>,<?php echo $comAd->address2; ?>,<?php echo $comAd->city; ?></td>
                            </tr>
                        </tbody>
                    </table>
        
    
                <br>
    
    <?php if(sizeof($addressactive) != 0 || sizeof($addresspending) != 0 || sizeof($editedrecs) != 0): ?>
            
                <table style="border: 0;" width="100%" autosize="1" >
                    <tbody>                    
                        <tr  >
                            <td style="border: 0;">RECORDS </td>
                            
                        </tr>
                        <tr>
                            <td style="border: 0; text-align:justify">To be completed if any of the records described in section 116(1) (a) to (i) of the Companies Act No. 7 of 2007 are not kept at the Company’s registered office or the place at which they are kept is changed.</td>
                              
                            </tr>
                       
                        </tbody>
                    </table>
                    <br>                   
            <table  style="border: 0;  word-break: break-all;" width="100%" autosize="1">
                <tr>
                    <td style="width:1.71in"  class="bg-color">Description of the Record</td>
                    <td style="width:1.71in"  class="bg-color">Date of change</td>
                    <td style="width:1.71in"  class="bg-color">Address where kept prior to change</td>
                    <td style="width:1.71in"  class="bg-color">Address where kept pursuant to change</td>
                </tr>
                <?php foreach($addressactive as $row): ?>
                <tr>
                    <td style="width:1.71in;" height="50"><?php echo str_replace([','],'<br>',str_replace(['"', '[', ']'],'',$row['description'])); ?></td>
                    <td style="width:1.71in" height="50"><?php echo $row['date']; ?></td>
                    <td style="width:1.71in" height="50"><?php echo $row['address1']; ?>,<?php echo $row['address2']; ?>,<?php echo $row['city']; ?></td>
                    <td style="width:1.71in" height="50"></td>
                </tr>
                <?php endforeach; ?>
                <?php foreach($editedrecs as $row): ?>
                <tr>
                    <td style="width:1.71in;" height="50"><?php echo $row['description']; ?></td>
                    <td style="width:1.71in" height="50"><?php echo $row['date']; ?></td>
                    <td style="width:1.71in" height="50"><?php echo $row['old_address']; ?></td>
                    <td style="width:1.71in" height="50"><?php echo $row['new_address']; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php foreach($addresspending as $row): ?>
                <tr>
                    <td style="width:1.71in;" height="50"><?php echo str_replace([','],'<br>',str_replace(['"', '[', ']'],'',$row['description'])); ?></td>
                    <td style="width:1.71in" height="50"><?php echo $row['date']; ?></td>
                    <td style="width:1.71in" height="50"><?php if($noRecActive){ echo $comAd->address1 .',' .$comAd->address2 .',' .$comAd->city; } ?></td>
                    <td style="width:1.71in" height="50"><?php echo $row['address1']; ?>,<?php echo $row['address2']; ?>,<?php echo $row['city']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
            <br>

            <table style="--primary-text-color: #212121; " width="100%" autosize="1">                    
                <tr  >
                    <td style="width:82pt;" height="40" class="bg-color">Full Name </td>
                    <td style="width:229pt;" height="40"><?php
                        $firstanme = $first_name;
                        $lastname = $last_name;
        
                        echo $firstanme . ' ' . $lastname;
        
                        ?></td>
                    {{-- <td style="width:28pt;"  height="40" rowspan="4"  style="text-rotate: 90;" align="center" class="bg-color">Signature</td>
                    <td style="width:170pt;" height="40" rowspan="4" ></td> --}}
                </tr>
                <tr >
                    <td style="width:82pt;" height="40" class="bg-color" >Email address</td>
                    <td style="width:229pt;"><?php echo $email;?> </td>
                </tr>
                <tr >
                    <td style="width:82pt;" height="40" class="bg-color" >Telephone No</td>
                    <td style="width:229pt;"><?php echo $telephone;?></td>
                </tr>
                <tr >
                    <td style="width:82pt;" height="40" class="bg-color">Mobile No</td>
                    <td style="width:229pt;"><?php echo $mobile;?></td>
                </tr>
                </tbody>
            </table>

            <br>
<?php if(sizeof($shareaddressactive) != 0 || sizeof($shareaddresspending) != 0 || sizeof($editedshares) != 0): ?>
            <table style="border: 0;" width="100%" autosize="1" >
                <tbody>                    
                    <tr >
                        <td style="border: 0;">SHARE REGISTER </td>
                        
                    </tr>
                    <tr>
                    <td style="border: 0; text-align:justify">To be completed if the share register is divided into 2 or more registers kept at different place or not
                                kept at the Company’s registered office or the place at which they are kept is changed.</td>
                      
                    </tr>
                   
                    </tbody>
                </table>
        <br>

            <table  style="border: 0" width="100%" autosize="1" >
                <tr>
                    <td style="width:1.71in"  class="bg-color">Description of share Register</td>
                    <td style="width:1.71in"  class="bg-color">Date of change </td>
                    <td style="width:1.71in"  class="bg-color">Address(es) where kept prior to change</td>
                    <td style="width:1.71in"  class="bg-color">Address(es) where kept pursuant to change</td>
                </tr>
                <?php foreach($shareaddressactive as $row): ?>
                <tr>
                    <td style="width:1.71in;" height="50"><?php echo str_replace([','],'<br>',str_replace(['"', '[', ']'],'',$row['description'])); ?></td>
                    <td style="width:1.71in" height="50"><?php echo $row['date']; ?></td>
                    <td style="width:1.71in" height="50"><?php echo $row['address1']; ?>,<?php echo $row['address2']; ?>,<?php echo $row['city']; ?></td>
                    <td style="width:1.71in" height="50"></td>
                </tr>
                <?php endforeach; ?>
                <?php foreach($editedshares as $row): ?>
                <tr>
                    <td style="width:1.71in;" height="50"><?php echo $row['description']; ?></td>
                    <td style="width:1.71in" height="50"><?php echo $row['date']; ?></td>
                    <td style="width:1.71in" height="50"><?php echo $row['old_address']; ?></td>
                    <td style="width:1.71in" height="50"><?php echo $row['new_address']; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php foreach($shareaddresspending as $row): ?>
                <tr>
                    <td style="width:1.71in;" height="50"><?php echo str_replace([','],'<br>',str_replace(['"', '[', ']'],'',$row['description'])); ?></td>
                    <td style="width:1.71in" height="50"><?php echo $row['date']; ?></td>
                    <td style="width:1.71in" height="50"><?php if($noShareActive){ echo $comAd->address1 .',' .$comAd->address2 .',' .$comAd->city; } ?></td>
                    <td style="width:1.71in" height="50"><?php echo $row['address1']; ?>,<?php echo $row['address2']; ?>,<?php echo $row['city']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <br>

            <table width="100%" autosize="1">
                <tbody>
                    <tr >
                        <td width="153pt" height="80" class="bg-color">Signature of <?php echo $member[0]['designation']; ?></td>
                        <td width="357pt" height="80"></td>
                    </tr>
                    <tr >
                        <td width="153pt" height="50" class="bg-color">Full Name of <?php echo $member[0]['designation']; ?></td>
                        <td width="357pt" height="50">&nbsp;<?php echo $member[0]['first_name']; ?>&nbsp;<?php echo $member[0]['last_name']; ?></td>
                    </tr>
                </tbody>
            </table>
            <br>

           
            <table  width="100%" height="50.4" style="border:0" autosize="1">
                    <tbody>
                        <tr>
                            <td height="30" align="left" style="border:0">Date:</td>
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
        <br>
        
       <div style="text-align:center">Notice should be given to the Registrar of Companies, within ten working days of the place or
places where the records are kept</div>
<br>

        <div  style="text-align:justify; font-size:12px;">* Indicate whether part share register or share register and if part share register the description of the relevant part. If
any records are not kept at the registered office or the place at which they are kept is changed notice should be filed
with the Registrar if Companies within Ten Working days of their first being kept elsewhere or moved.
</div>
</body>
</html>