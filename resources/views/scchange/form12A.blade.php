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
            
            .bd{
                border:0;
            }	
            font {
                margin-left: 0px;
                margin-right: 0px;
                font-size: 14px;
                font-family: 'SegoeUI', sans-serif;
                margin-bottom: 1px;
            }

            tr.spaceUnder>td {
            padding-bottom: 1em;
            Z-index: 1;
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
                font-family: 'SegoeUI', sans-serif;

            }
        </style>
    </head>
    
    <body>
        <section class="form-body">
            
            <table width="100%" style="border:0; padding:0;" autosize="1">
                <tr>
                    <td width="20%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                    <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 12A<br><br></b></span>
                    <p style="font-size:16px;"><b>DECLARATION VERIFYING MEMORANDUM OF SATISFACTION OF CHARGE TO ENABLE THE REGISTRAR-GENERAL OF COMPANIES <br>
                                                                        TO ACT UNDER SECTION 107 OF THE COMPANIES ACT NO. 7 OF 2007</b></p></td>
                    <td width="10%" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 4(1))</td>
                    <td width="20%" style="border:0; padding:0px;"><img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
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
            <p>
                We,(note)&nbsp; <b><?php echo $member[0]['first_name']; ?>&nbsp;<?php echo $member[0]['last_name']; ?></b> &nbsp; of &nbsp; <b> <?php echo $member[0]['address']; ?></b>
                and &nbsp;<b><?php echo $member[1]['first_name']; ?>&nbsp;<?php echo $member[1]['last_name']; ?></b>&nbsp; of &nbsp; <b> <?php echo $member[1]['address']; ?></b>
                Directors of &nbsp; <b> <?php echo $comName; ?>&nbsp;<?php echo $comPostfix; ?> </b>
                thereof do hereby make oath and say solemnly sincerely and truly
                affirm and state that the particulars (note 1a) contained in the
                Memorandum of Satisfaction annexed hereto and dated the &nbsp;<?php echo substr($satisfaction_date,0,4); ?>&nbsp; day of &nbsp;<?php echo substr($satisfaction_date,10); ?> &nbsp; <?php echo substr($satisfaction_date,5,4); ?>&nbsp; is true to the best
                of our knowledge, information and belief, and we make this solemn Declaration, conscientiously
                believing the same to be true.
                <br>
                <br>
                We further agree and undertake to furnish documentary evidence to prove the aforesaid satisfaction if called upon by the Registrar-General of Companies.
                <br>
                <br>
                Declared at ......................................... the ............... day of ............................. 20........ before me



            </p>
        
        
            
            <br>
            
            <table width="100%" style="border: 0" autosize="1">
                <tbody>
                    <tr>
                        <td style="border: 0">..............................................................................<br>A Justice of the Peace or Commissioner for Oaths</td>
                        <td style="border: 0"><img width="130" height="auto" src="{{  URL::to('/') }}/images/redSeal.jpg" alt="seal"></td>
                    </tr>
                </tbody>
            </table>

            <br>

            <table width="100%" style="border:0" autosize="1">

                <tbody>
                    <tr>
                        <td align="center" style="border: 0">
                            <span style="font-size:16px;"><b>MEMORANDUM OF SATISFACTION OF CHARGE</b></span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p>
                    I/We hereby gives notice that the registered charge being (Note) &nbsp;<b><?php echo $inDetails; ?></b>
                    of which particulars were registered with the Registrar-General of Companies on the &nbsp;<?php echo substr($inDate,0,4); ?>&nbsp; day of
                    &nbsp;<?php echo substr($inDate,10); ?> &nbsp; <?php echo substr($inDate,5,4); ?>&nbsp; was satisfied on the &nbsp;<?php echo substr($endDate,0,4); ?>&nbsp; day of &nbsp;<?php echo substr($endDate,10); ?> &nbsp; <?php echo substr($endDate,5,4); ?>&nbsp; to the full extent of &nbsp;<b><?php if($full_ex){echo $full_ex;}else{echo "nil";} ?></b> 
                    and/or that(Note) &nbsp;<b><?php echo $property_details; ?></b> has been released
                    from the charge or has ceased to form part of a company’s property or undertaking

                
            </p>
            <br>
            <table width="100%" style="border:0;">

                <tbody>
                    <tr class="spaceUnder">
                        <td style="border: 0;padding-bottom: 2em;">Director  :-</td>
                    </tr>

                    <tr class="spaceUnder">
                        <td style="border: 0;padding-bottom: 2em;">Director  :-</td>
                    </tr>

                    <tr class="spaceUnder">
                        <td style="border: 0;padding-bottom: 2em;">OR</td>
                    </tr>

                    <tr class="spaceUnder">
                        <td style="border: 0;padding-bottom: 2em;">Director  :-</td>
                    </tr>

                    <tr>
                        <td style="border: 0;padding-bottom: 2em;">Secretary :-</td>
                    </tr>
                </tbody>
            </table>
            
            <br>
            <span>Presented by</span>
            <table style="--primary-text-color: #212121; " width="100%" style="border:0;" autosize="1">
                <tbody>
                    {{-- <tr>
                         <td style="border:0;" colspan="4"><b>Presented by:</b></td>
                    </tr> --}}
                    
                    <tr> 
                        <td width="82pt" height="40" class="bg-color">Full Name </td>
                        <td width="229pt" height="40">&nbsp;<?php
                            $firstanme = $first_name;
                            $lastname = $last_name;
            
                            echo $firstanme . ' ' . $lastname;
            
                            ?></td>
                        {{-- <td width="30pt"  height="40" rowspan="5" style="text-rotate: 90;" align="center" class="bg-color">Signature</td>
                        <td width="168pt" height="40" rowspan="5" ></td> --}}
                    </tr>

                    <tr >
                        <td width="82pt"  class="bg-color"  height="40">E Mail Address</td>
                        <td width="229pt">&nbsp;<?php echo $email;?></td>
                    </tr>

                    <tr >
                        <td width="82pt" class="bg-color"  height="40">Telephone No</td>
                        <td width="229pt">&nbsp;<?php echo $telephone;?></td>
                    </tr>

                    <tr >
                        <td width="82pt" class="bg-color"  height="40">Mobile No</td>
                        <td width="229pt" >&nbsp;<?php echo $mobile;?></td>
                    </tr>
                </tbody>
            </table>
            
            <br>
            
            <table style="border-color: #FFFFFF;" width="100%" height="30" autosize="1">
                <tbody>
                    <tr>
                        <td style="border:0;" width="5%" valign="top">(1) </td>
                        <td style="border:0; word-break: break-all;">This declaration and Memorandum should be executed by two Directors or a Director and the Secretary of the Company and the declaration should be sworn or affirmed to before a Justice of the Peace or Commissioner of Oaths.<br>(1a) . Delete what is not applicable.</td>
                    </tr>
                    <tr>
                        <td style="border:0;" width="5%" valign="top">(2) </td>
                        <td style="border:0; word-break: break-all;">A description of the Instruments creating or evidencing the charge, with the date thereof should be given.<br>If the registered charge as a “Series of Debentures” the words “authorised by Resolution”, together with the date of the resolution should be added.</p></td>
                    </tr>
                    <tr>
                        <td style="border:0;" width="5%" valign="top">(3) </td>
                        <td style="border:0; word-break: break-all;">To give brief details of property, vide Sec. 107(b)</p></td>
                    </tr>
                    
                    
                </tbody>
            </table>

        </section>

    </body>

</html>