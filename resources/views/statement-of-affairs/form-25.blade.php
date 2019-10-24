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
                font-size: 13px;
                
                padding: 5px;
                font-family: 'SegoeUI', sans-serif;
            }

            /* table{
            page-break-inside: avoid;
            table-layout:fixed;
            } */

            font {
                margin-left: 0px;
                margin-right: 0px;
                font-size: 13px;
                font-family: 'SegoeUI';
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

            body {
                margin-left: 2.5cm;
                font-family: 'SegoeUI', sans-serif;
            }

            .td0{
            
                border-top: 0;
                border-bottom:0;
            
            }
                
            .td1{
                border-top:0 ;
                border-right:0;
                border-bottom:0;
                border-left:;
            }
                        
            .td2{
                border-top: 0;
                border-right:;
                border-bottom:0;
                border-left:0;
            }

            .td3{
                border-top: 0;
                border-right:;
                border-bottom:;
                border-left:0;
            }

            .td4{
                border-top: ;
                border-right:;
                border-bottom:dotted;
                border-left:;
            }

            .td5{
                border-top:dotted ;
                border-right:dotted;
                border-bottom:dotted;
                border-left:dotted;
            }

            .td6{
                border-top:dotted ;
                border-right:;
                border-bottom:dotted;
                border-left:;
            }

            .td7{
                border-top:dotted ;
                border-right:;
                border-bottom:;
                border-left:;
            }

            .td8{
                border-top:0 ;
                border-right:;
                border-bottom:0;
                border-left:;
                }
					
        </style>
    </head>

    <body>

    <section class="form-body">
        {{-- <!-- {{ $foo }} --> --}}
        <table width="100%" style="border:0; padding:0;">
            <tr>
                <td width="101pt" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{  URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
                <td width="50%" style="border:0; font-size: 18px; padding-top:20px; padding-left:50px;" align="center"><span><b>FORM 25<br><br></b></span><p style="font-size:16px;"><b>STATEMENT OF AFFAIRS </b></p></td>
                <td width="51pt" style="border:0; padding:0px; font-size: 10px;" align="left">(Section 283(1))</td>
                <td width="101pt" style="border:0; padding:0px;"><img width="130" height="auto" src="{{  URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
            </tr>
            <!-- <tr>
                <td colspan="4" align="center" style="border:0; font-size:15px; padding:0;"><b>REGISTRATION OF A COMPANY</b> </td>
            </tr> -->
            <tr>
                <td colspan="4" align="center" style="border:0; padding:0px; font-size:13px;">Section 283(1) of Companies Act No.7 of 2007</td>
            </tr>
        </table>
        <br>
        

        <table style="border: 0;" width="100%">
            <tbody>
                <tr>
                    <td width="100%" style="border: 0;">[If there is insufficient space on the form to supply the information required, attach a separate sheet
                    containing the information set out in the prescribed format]</td>
                    
                </tr>
            </tbody>
        </table>
        
        <br>

         <table style="border: 0;" width="100%" >
            <tbody>
                <tr>
                    <td width="23%" height="40" style="border: 0;"> Company Number</td>
                    <td width="38pt"  height="40" class="bg-color">&nbsp;<?php echo $regNo[0];?></td>
                    <td width="38pt"  height="40" class="bg-color">&nbsp;<?php echo $regNo[1];?></td>
                    <td width="49%" height="40" class="bg-color">&nbsp;<?php echo substr($regNo, 2); ?></td>
					<td width="38pt" height="40" class="bg-color">&nbsp;</td>
					<td width="38pt" height="40" class="bg-color">&nbsp;</td>
                </tr>
            </tbody>
        </table>
        <br>

        <table style="border: 0;" width="100%" >
            <tbody>
                <tr>
                    <td width="23%" height="40" style="border: 0;">Company Name </td>
                    <td width="77%" height="40">&nbsp; <?php echo $comName; ?>&nbsp;<?php echo $comPostfix; ?></td>
                </tr>
            </tbody>
        </table>

        <br>

        <table style="border: 0;" width="100%" autosize="1">
            <tbody>
                <tr>
                    <td colspan="6" width="408pt"></td>
                    <td width="101pt">Estimated Realizable Value</td>
                </tr>

                <tr>
                    <td colspan="6" width="408pt" height="20" class="td0"><u><b>Assets not specifically pledged</b></u></td>
                    <td width="101pt" class="td0"></td>
                </tr>

                <tr>
                    <td colspan="6" width="408pt" class="td0">Balance at Bank</td>
                    <td width="101pt" class="td0"></td>
                </tr>

                <tr>
                    <td colspan="6" width="408pt" class="td0">Cash in Hand</td>
                    <td width="101pt" class="td0" ></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">Marketable securities</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">Bills receivable</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">Trade Debtors</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">Loans and advances</td>
                    <td width="101pt" class="td0"></td>
                </tr><tr>
                    <td colspan="6" width="408pt" class="td0">Unpaid calls</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">Stock in Trade</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">Work in progress</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">.........................................</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">.........................................</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">.........................................</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">.........................................</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">.........................................</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">.........................................</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">Freehold property</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">Leasehold property</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">Plant and machinery</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">Furniture, Fittings, Utensils, etc.,</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">Patents, Trade Marks, etc.,</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">Investments other than marketable securities</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">Other property, viz</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">.........................................</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">.........................................</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">.........................................</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">.........................................</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">.........................................</td>
                    <td width="101pt" class="td0"></td>
                </tr>
                <tr>
                    <td colspan="6" width="408pt" class="td0">.........................................</td>
                    <td width="101pt" class="td0"></td>
                </tr>

                <tr>
                    <td colspan="2" class="td0"><b><u>Assets specifically pledged</u></b></td>
                    <td>(a) Estimated Realisable Values (Rs.)</td>
                    <td>(b) Due to secured creditors (Rs.)</td>
                    <td>(c) Deficiency ranking as unsecured (see next page) (Rs.)</td>
                    <td>(d) Surplus carried to last column (Rs.)</td>
                    <td width="101pt" class="td0"></td>
                    
                </tr>

                <tr>
                    <td colspan="2" class="td0">Freehold property</td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                    <td  width="101pt" class="td0"></td>
                    
                </tr>
                <tr>
                    <td colspan="2" class="td0">.........................................</td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                    <td width="101pt" class="td0"></td>
                    
                </tr>
                <tr>
                    <td colspan="2" class="td0"> .........................................</td>
                    <td width="61pt" class="td0"></td>
                    <td width="61pt" class="td0"></td>
                    <td width="61pt" class="td0"></td>
                    <td width="62pt" class="td0"></td>
                    <td width="101pt" class="td0"></td>
                   
                </tr>
                <tr>
                    <td colspan="2" class="td0"> .........................................</td>
                    <td width="61pt" class="td0"></td>
                    <td width="61pt" class="td0"></td>
                    <td width="61pt" class="td0"></td>
                    <td width="62pt" class="td0"></td>
                    <td width="101pt" class="td0"></td>
                   
                </tr>

                <tr>
                    <td class="td0" style="border-right:0"></td>
                    <td class="td0" style="border-left:0"></td>
                    <td>Rs.</td>
                    <td>Rs.</td>
                    <td>Rs.</td>
                    <td>Rs.</td>
                    <td class="td0" ></td>
                </tr>

                <tr>
                    <td colspan="6">Estimated surplus from Assets specifically pledged <br><br>
                        <b>Estimated total Assets Available for preferential creditors, debenture holders secured by a
                        floating charge, and unsecured creditors* (carried forward to next page)</b><br><br>Summary of Gross Assets 
                        </td>
                    <td class="td0"></td>
                    
                </tr>

                <tr>
                    <td colspan="5" class="td0">1. Gross realizable value of assets specifically other Assets</td>
                    <td style="border-bottom:0"></td>
                    <td class="td0"></td>
                    
                    
                </tr>

                <tr>
                    
                    <td colspan="5" class="td0">2. Pledged</td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                    
                </tr>

                <tr>
                    <td colspan="5" class="td0">3. Gross Assets</td>
                    <td style="border-top:0"></td>
                    <td class="td0"></td>
                    
                </tr>

                <tr>
                    <td colspan="5" class="td0"></td>
                    <td>Rs.</td>
                    <td class="td0"></td>
                </tr>

                <tr>
                    <td colspan="6"><b>Estimated total assets available for preferential creditors, debenture holders secured by a floating charge, and unsecured creditors* (brought forward from preceding page)</b></td>
                    <td style="border-bottom:0"></td>
                    
                </tr>

                <tr>
                    <td class="td0">E. Gross Liabilities <br>Rs.</td>
                    <td colspan="5" align="center" class="td0"><b><u>Liabilities</u><br></b><b>(to be deducted from surplus or added to deficiency as the case may be)</td>
                    <td class="td0"></td>
                    
                </tr>
                <tr>
                    <td class="td0"></td>
                    <td colspan="5" class="td0"><b>Secured Creditors</b> (as per list “B”) to the extent to which claims are estimated to be covered by Assets specifically pledged (item (a) or (b) on preceding page whichever is the less)<br> (inset in “Gross Liabilities” column only)</td>
                    <td class="td0"></td>
                </tr>           
                <tr>
                    <td height="25" class="td0"></td>
                    <td colspan="5" class="td0"></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="td0"></td>
                    <td colspan="4" class="td1"><b>Preferential Creditors</b> (as per List “C”) Estimated balance of assets available for Debenture Holders secured by a floating charge and unsecured creditors* </td>
                    <td class="td2">Rs.</td>
                    <td style="border-bottom:0"></td>
                    
                </tr>

                <tr>
                    <td class="td0"></td>
                    <td colspan="4" class="td1">Debenture Holders secured by a floating charge (as per list “D”)</td>  
                    <td class="td2"></td>
                    <td style="border-top:0"></td>  
                </tr>

                <tr>
                    <td class="td0"></td>
                    <td colspan="4" style="border-top:0; border-right:0">Estimated Surplus/Deficiency as regards Debenture Holders *</td>
                    <td class="td3">Rs.</td> 
                    <td class="td0"></td>                     
                </tr>

                <tr>
                    <td class="td0"></td>
                    <td colspan="4" style="border-bottom:0" ><u>Unsecured creditor (as per List “E”):-</u><br>Estimated unsecured balance of claims of Creditors partly secured on specific assets, brought from preceding page (c) </td>
                    <td valign="top" style="border-bottom:0">Rs.</td>
                    <td style="border-bottom:0"></td>
                    
                </tr>

                <tr>
                    <td class="td0"></td>
                    <td colspan="4" class="td0">Trade Accounts</td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                </tr>

                <tr>
                    <td class="td0"></td>
                    <td colspan="4" class="td0">Bills Payable </td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                    
                </tr>

                <tr>
                    <td class="td0"></td>
                    <td colspan="4" class="td0">Outstanding Expenses </td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                   
                </tr>
                <tr> 
                    <td class="td0" height="30"></td>
                    <td colspan="4" height="30" class="td0" ></td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                </tr>
                <tr>
                    <td class="td0"></td>
                    <td colspan="4" class="td0">Contingent Liabilities (State nature)</td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                    
                </tr>
                <tr> 
                    <td class="td0" height="30"></td>
                    <td colspan="4" class="td0"></td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                </tr>
                <tr>
                    <td class="td0"></td>
                    <td colspan="4" class="td0"><u><b>Estimated Surplus/Deficiency as regards Creditors*</u></b></u></td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                </tr>

                <tr>
                    <td class="td0"></td>
                    <td colspan="4" class="td0">being difference between: Gross Assets brought from preceding page (d) and Gross Liabilities as per column (e) </td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                   
                </tr>

                <tr>
                    <td height="40" style="border-bottom:double" valign="top">Rs.</td>
                    <td colspan="4" class="td0" ></td>
                    <td class="td0" ></td>
                    <td class="td0" ></td>
                    
                </tr>

                <tr>
                    <td style="border-bottom:0" ></td>
                    <td colspan="4" class="td0"><u>Issued and called-up Capital</u> </td>
                    <td style="border-bottom:0" >Rs.</td>
                    <td style="border-bottom:0" ></td>
                    
                </tr>

                <tr>
                    <td class="td0" ></td>
                    <td colspan="4" class="td0">Preference shares of…………each called-up (as per List “F”)</td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                    
                </tr>

                <tr>
                    <td class="td0"></td>
                    <td colspan="4" class="td0">ordinary share of …………. each called-up (as per List “G”</td>
                    <td class="td0"></td>
                    <td class="td0"></td>
                    
                </tr>

                <tr>
                    <td style="border-top:0"></td>
                    <td colspan="4" style="border-top:0" align="right"><b>estimated surplus/Deficiency as regards Members*</b> </td>
                    <td>Rs.</td>
                    <td></td>       
                </tr>
                
            </tbody>
        </table>

        <br>

        <table style="border:0" width="100%" autosize="1">
            <tr>
                <td style="border:0" rowspan="2" width="5%" valign="top">(1)</td>
                <td style="border:0">(f)   There is no unpaid capital liable to be called up or </td>
            </tr>
            <tr>
                <td style="border:0">(g)   The nominal amount of unpaid capital liable to be called up is Rs. ______ estimated to produce Rs. ______ which is/is not charged in favor of Debenture Holders </td>
            </tr>
            <tr>
                <td style="border:0" rowspan="2" width="5%" valign="top">(2)</td>
                <td style="border:0">The estimates are subject to costs of the winding-up and to any surplus or deficiency on trading pending realization of the Assets.</td>
            </tr>
            <tr>
                <td style="border:0" colspan="2" height="10"></td>
            </tr>    
    
        </table>

        <br>
        
        <table style="border:0" width="100%" autosize="1">

            <tr>
                <td style="border:0" colspan="8"><b><u>Assets not specifically pledged</u></b></td>
            </tr>
            <tr>
                <td style="border:0" colspan="8">Full particulars of every description of properly not specifically pledged and not included in any other list are to be set forth in this list </td>
            </tr>

            <tr>
                <td width="51pt"></td>
                <td width="153pt"><b>Full statement and nature of property</b> </td>
                <td width="153pt" colspan="3" align="center">Book value </td>
                <td width="153pt" colspan="3" align="center">Estimated to produced </td>
                
            </tr>

            <tr>
                <td width="51pt" rowspan="7" valign="top" style="border-bottom:0">State name of bankers </td>
                <td width="153pt" class="td4">Balance at bank </td>
                <td width="51pt" style="border-right:dotted"></td>
                <td width="51pt" style="border-right:dotted"></td>
                <td width="51pt" ></td>
                <td width="51pt" style="border-right:dotted"></td>
                <td width="51pt" style="border-right:dotted"></td>
                <td width="51pt" ></td>
            </tr>

            <tr>
                <td width="153pt" class="td4">Cash in hand </td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                
            </tr>

            <tr>
                <td width="153pt" class="td4">Marketable securities, Viz: -</td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                
            </tr>

            <tr>
                <td width="153pt" class="td4">Bills receivable (as per schedule I)</td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                
            </tr>

            <tr>
                <td width="153pt" class="td4">Trade debtors (as per schedule II)</td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                
            </tr>

            <tr>
                <td width="153pt" class="td4">Loans and advances, Viz: -</td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                
            </tr>

            <tr>
                <td width="153pt" class="td4">Unpaid calls (as per schedule III)</td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                
            </tr>

            <tr>
                <td width="51pt" rowspan="9" valign="top" style="border-top:0">State nature </td>
                <td width="153pt" class="td4">Stock in trade </td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                
            </tr>

            <tr>
                <td width="153pt" class="td4">Work in progress</td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                
            </tr>

            <tr>
                <td width="153pt" class="td4">Freehold property, Viz: -</td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                
            </tr>

            <tr>
                <td width="153pt" class="td4">Leasehold property, Viz: -</td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                
            </tr>

            <tr>
                <td width="153pt" class="td4">Plant & machinery, Viz: -</td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                
            </tr>

            <tr>
                <td width="153pt" class="td4">Furniture, fittings, utensils, Etc.,</td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5" ></td>
                <td width="51pt" class="td6"></td>
                
            </tr>

            <tr>
                <td width="153pt" class="td4">Patents, trademarks, etc., Viz: -</td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                
            </tr>

            <tr>
                <td width="153pt" class="td4">Investments other than marketable securities Viz: -</td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td6"></td>
                <td width="51pt" class="td5"></td>
                <td width="51pt" class="td6"></td>
                
            </tr>

            <tr>
                <td width="153pt">Other property, Viz: -</td>
                <td width="51pt" style="border-right:dotted"> </td>
                <td width="51pt" style="border-right:dotted"></td>
                <td width="51pt"></td>
                <td width="51pt" style="border-right:dotted"></td>
                <td width="51pt" style="border-right:dotted"></td>
                <td width="51pt"></td>
                
            </tr>

        </table>
        
        <br>

        <table style="border:0" width="100%" autosize="1">
            <tr>
                <td style="border:0" colspan="11"><b><u>Bills of Exchange, Promissory Notes, &c., (on Hand Available) as Assets</u><b> </td>
            </tr>

            <tr>
                <td style="border:0" colspan="11">The names to be arranged in alphabetical ordered and numbered consecutively</td>
            </tr>

            <tr>
                <td width="38pt" valign="top" rowspan="2">No.</td>
                <td width="55pt" valign="top" rowspan="2">Name of Acceptor of Bill or Note </td>
                <td width="54pt" valign="top" rowspan="2">Address, &c.</td>
                <td width="115pt" colspan="3" valign="top">Amount of Bill or Note </td>
                <td width="58pt" rowspan="2" valign="top">Date<br> when<br> due</td>
                <td width="115pt" colspan="3" valign="top">Estimated to produce </td>
                <td width="74pt" rowspan="2" valign="top">Particulars of any property held as security for payment of Bill or Note </td> 
            </tr>

            <tr>
                <td width="38pt" ></td>
                <td width="38pt" ></td>
                <td width="38pt" ></td>
                <td width="38pt" ></td>
                <td width="38pt" ></td>
                <td width="38pt" ></td>
            </tr>

            <tr>
                <td  height="175"></td>
                <td></td>
                <td></td>
                <td valign="top">Rs.</td>
                <td></td>
                <td></td>
                <td></td>
                <td valign="top">Rs.</td>
                <td></td>
                <td></td>
                <td></td>

            </tr>

        </table>

        <br>

        <table width="100%" style="border:0" autosize="1">
            <tr>
                <td colspan="2" style="border:0"><b><u>Trade debtor</u></b></td>
            </tr>

            <tr>
                <td colspan="2" style="border:0">The names to be arranged in alphabetical order and numbered consecutively.</td>
            </tr>

            <tr>
                <td colspan="2" style="border:0"><b>Note:</b> If the debtor to the company is also a creditor, but for a less amount than his indebtedness, the gross amount due to the company and the amount of the contra account should be shown in the third column, and the balance only be inserted under the heading “Amount of Debt” thus:</td>
            </tr>

            <tr>
                <td width="153pt" style="border:0">Due to Company</td>
                <td width="357pt" style="border:0">Rs.</td>
            </tr>

            <tr>
                <td colspan="2" style="border:0">Less: contra account</td>
            </tr>

            <tr>
                <td colspan="2" style="border:0">No such claim should be included in List “E”  </td>
            </tr>
        </table>

        <br>

        <table width="100%" style="border:0" autosize="1">
            <tr>
                <td rowspan="2" align="center" valign="top">No.</td>
                <td rowspan="2" align="center" valign="top">Name</td>
                <td rowspan="2" align="center" valign="top">Residence <br>&<br> occupation</td>
                <td colspan="9" align="center" valign="top">Amount of Debt</td>
                <td rowspan="2" align="center" valign="top">Folio Ledger or other book where particulars to be found</td>
                <td colspan="2" align="center" valign="top">When contracted</td>
                <td colspan="3" align="center" valign="top" rowspan="2">Estimated to produce</td>
                <td align="center" valign="top" rowspan="2">Particulars of any securities held for debt</td>
            </tr>

            <tr>
                <td colspan="3" align="center" valign="top" >Good</td>
                <td colspan="3" align="center" valign="top">Doubtful</td>
                <td colspan="3" align="center" valign="top">Bad</td>
                <td align="center" valign="top">Month</td>
                <td align="center" valign="top">Year</td>
            </tr>

            <tr>
                <td width="24pt"></td>
                <td width="33pt"></td>
                <td width="47pt"></td>
                <td width="20pt" height="150" valign="top"><strong style="font-size:11px;">Rs.</strong></td>
                <td width="19pt"></td>
                <td width="19pt"></td>
                <td width="20pt" valign="top"><strong style="font-size:11px;">Rs.</strong></td>
                <td width="19pt"></td>
                <td width="19pt"></td>
                <td width="20pt" valign="top"><strong style="font-size:11px;">Rs.</strong></td>
                <td width="19pt"></td>
                <td width="19pt"></td>
                <td width="45pt"></td>
                <td width="38pt"></td>
                <td width="38pt"></td>
                <td width="20pt" valign="top"><strong style="font-size:11px;">Rs.</strong></td>
                <td width="19pt"></td>
                <td width="19pt"></td>
                <td width="50pt"></td>
            </tr>
        </table>
        
        <br>

        <table width="100%" style="border:0" autosize="1">
            <tr>
                <td colspan="14" style="border:0"><u><b>Update called</b></u></td>
            </tr>

            <tr>
                <td colspan="14" style="border:0">The names to be arranged in alphabetical order and numbered consecutively</td>
            </tr>

            <tr>
                <td width="">Consecutive No.</td>
                <td>No. in share register </td>
                <td>Name of shareholder </td>
                <td>Address</td>
                <td>No. of Share held </td>
                <td colspan="3">Amount of call per share unpaid </td>
                <td colspan="3">Total amount due </td>
                <td colspan="3">Estimated to realize </td>
            </tr>

            <tr>
                <td width="31pt" height="150"></td>
                <td width="51pt"></td>
                <td width="50pt"></td>
                <td width="51pt"></td>
                <td width="51pt"></td>
                <td width="31pt" valign="top" align="left" style="font-size:11px">Rs.</td>
                <td width="31pt"></td>
                <td width="31pt"></td>
                <td width="31pt"valign="top" align="left" style="font-size:11px">Rs.</td>
                <td width="31pt"></td>
                <td width="31pt"></td>
                <td width="31pt"></td>
                <td width="31pt"></td>
                <td width="31pt"></td>
            </tr>

            <tr>
                <td colspan="14" style="border:0" height="5"></td>
            </tr>

            <tr>
                <td colspan="14" style="border:0">Assets specifically pledged and creditors fully or partly secured<br><u>(Not including Debenture Holders secured by a floating charge)</u></td>
            </tr>

            <tr>
                <td colspan="14" style="border:0">The names of the secured creditors are to be shown against the assets on which their claims are secured, numbered consecutively, and arranged in alphabetical order as far as possible.</td>
            </tr>

        </table>

        <br>

        <table width="100%" style="border:0" autosize="1">
            <tr>
                <td rowspan="2">Particulars of assets specifically pledged </td>
                <td rowspan="2" style="border-bottom:0">Date when given security </td>
                <td colspan="3" rowspan="2">Estimated value of security</td>
                <td rowspan="2">No</td>
                <td rowspan="2">Name of Creditor</td>
                <td rowspan="2">Address & occupation</td>
                <td colspan="3" rowspan="2">Amount of debt </td>
                <td colspan="2">Date when contracted</td>
                <td rowspan="2">Consideration</td>
                <td rowspan="2" colspan="3">Balance of debt un-secured carried to list “E”</td>
                <td rowspan="2" colspan="3">Estimated surplus from security </td>
            </tr>

            <tr>
                <td style="border-right:0, border-top:0"><span style="font-size:11px">Month</span></td>
                <td style="border-left:0, border-top:0"><span style="font-size:11px">Year</span></td>
            </tr>

            <tr>
                <td height="75" style="border-bottom:0" width="51pt"></td>
                <td style="border-bottom:0" width="51pt"></td>
                <td style="border-bottom:0" width="19pt"><span style="font-size:11px">Rs</span></td>
                <td style="border-bottom:0" width="19pt"></td>
                <td style="border-bottom:0" width="19pt"></td>
                <td style="border-bottom:0" width="19pt"></td>
                <td style="border-bottom:0" width="12%"></td>
                <td style="border-bottom:0" width="12%"></td>
                <td style="border-bottom:0" width="19pt"><span style="font-size:11px">Rs</span></td>
                <td style="border-bottom:0" width="19pt"></td>
                <td style="border-bottom:0" width="19pt"></td>
                <td style="border-bottom:0" width="6%"></td>
                <td style="border-bottom:0" width="6%"></td>
                <td style="border-bottom:0" width="12%"></td>
                <td style="border-bottom:0" width="19pt"><span style="font-size:11px">Rs</span></td>
                <td style="border-bottom:0" width="19pt"></td>
                <td style="border-bottom:0" width="19pt"></td>
                <td style="border-bottom:0" width="19pt"><span style="font-size:11px">Rs</span></td>
                <td style="border-bottom:0" width="19pt"></td>
                <td style="border-bottom:0" width="19pt"></td>
            </tr>

            <tr>
                <td height="75" style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
            </tr>
        </table>

        <br>

        <table width="100%" style="border:0" autosize="1">
            <tr>
                <td style="border:0">Preferential creditors for rates, taxes, salaries, wages and otherwise statement of Affairs </td>
            </tr>

            <tr>
                <td height="8" style="border:0"></td>

            <tr>
                <td style="border:0">Preferential creditors for rates, taxes, salaries, wages and otherwise statement of Affairs The name to be arranged in alphabetical order and numbered consecutively.</td>
            </tr>

        </table>

        <br>

        <table width="100%" style="border:0" autosize="1"> 
            <tr>
                <td>No</td>
                <td>Name of Creditor</td>
                <td>Address & Occupation</td>
                <td>Nature of Claim</td>
                <td>Period during which claim accrued due </td>
                <td>Date when due </td>
                <td colspan="3">Amount of claim </td>
                <td colspan="3">Amount payable in full </td>
                <td colspan="3">Balance not preferential carried to list “E”</td>
            </tr>

            <tr>
                <td width="25pt" valign="top" height="100"></td>
                <td width="51pt" valign="top"></td>
                <td width="51pt" valign="top"></td>
                <td width="51pt" valign="top"></td>
                <td width="51pt" valign="top"></td>
                <td width="51pt" valign="top"></td>
                <td width="26pt" valign="top"></td>
                <td width="25pt" valign="top"></td>
                <td width="26pt" valign="top"></td>
                <td width="25pt" valign="top"></td>
                <td width="26pt" valign="top"></td>
                <td width="25pt" valign="top"></td>
                <td width="26pt" valign="top"></td>
                <td width="25pt" valign="top"></td>
                <td width="26pt" valign="top"></td>
            </tr>
        </table>

        <br>

        <table width="100%" style="border:0" autosize="1">
            <tr>
                <td colspan="7" style="border:0"><u>List of Debenture Holders secured by a floating charge-statement of affairs</u> </td>
            </tr>

            <tr>
                <td colspan="7" style="border:0">The names to be arranged in alphabetical order and numbered consecutively. Separate lists must be furnished of holders of each issue of Debentures, should more than one issue have been made.</td>
            </tr>

            <tr>
                <td>No.</td>
                <td>Name of Holder</td>
                <td>Address</td>
                <td colspan="3">Amount</td>
                <td>Description of assets over which security extends </td>
            </tr>

            <tr>
                <td width="41pt" valign="top" height="150"></td>
                <td width="112pt" valign="top"></td>
                <td width="112pt" valign="top"></td>
                <td width="41pt" valign="top"></td>
                <td width="40pt" valign="top"></td>
                <td width="41pt" valign="top"></td>
                <td width="122pt" valign="top"></td> 
            </tr>
        </table>

        <br>
 
        <table width="100%" style="border:0" autosize="1">
            <tr>
                <td colspan="4" style="border:0"><b><u>Unsecured Creditors</u></b></td>
            </tr>

            <tr>
                <td colspan="4" style="border:0">The names to be arranged in alphabetical order and numbered consecutively.</td>
            </tr>

            <tr>
                <td colspan="4" style="border:0"><b>Notes</b> : 1. When there is a contra account against the creditor less his claim against the company, the amount of the creditor’s claim and the amount of the contra account should be shown in the third column and the balance only be inserted under the heading “Amount of Debt” thus:-</td>
            </tr>

            <tr>
                <td align="right" style="border:0">Total amount of claim </td>
                <td align="right" style="border:0">Rs.</td>
                <td style="border:0"></td>
                <td style="border:0"></td>
            </tr>

            <tr>
                <td align="right" style="border:0" >Less: contra account </td>
                <td style="border:0"></td>
                <td style="border:0"></td>
                <td style="border:0"></td>
            </tr>

            <tr>
                <td colspan="4" style="border:0">No such set-off should be included in schedule I attached to list “A” </td>
            </tr>

            <tr>
                <td colspan="4" style="border:0">2. The particulars of any Bills of Exchange and Promissory Notes held by a creditor should be inserted immediately below the name and address of such creditor.</td>
            </tr>

            <tr>
                <td colspan="4" style="border:0">[Unsecured balance of creditors partly secured – brought from list “B”]<br>[Balance not preferential of preferential creditors - brought from List “C”]</td>
            </tr>
        </table>

        <br>

        <table width="100%" style="border:0" autosize="1">
            <tr>
                <td rowspan="2">No.</td>
                <td rowspan="2">Name</td>
                <td rowspan="2">Address & occupation </td>
                <td colspan="3">Amount of Debt</td>
                <td colspan="2">Date when contracted</td>
                <td rowspan="2">Consideration </td>
            </tr>

            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>Month</td>
                <td>Year</td>
            </tr>

            <tr>
                <td width="51pt" height="150"></td>
                <td width="97pt"></td>
                <td width="97pt"></td>
                <td width="30pt"></td>
                <td width="31pt"></td>
                <td width="30pt"></td>
                <td width="41pt"></td>
                <td width="41pt"></td>
                <td width="91pt"></td>
            </tr>
        </table>

        <br>

        <table width="100%" style="border:0" autosize="1">
            <tr>
                <td colspan="12" style="border:0"><b><u>List of preference shareholders</u></b></td>
            </tr>

            <tr>
                <td colspan="12" style="border:0">The names to be arranged in alphabetical order and numbered consecutively </td>
            </tr>

            <tr>
                <td>Consecutive No. </td>
                <td>Register No.</td>
                <td>Name of Shareholder </td>
                <td>Address</td>
                <td>Nominal amount of share</td>
                <td>No. of shares held </td>
                <td colspan="3">Amount per share called-up </td>
                <td colspan="3">Total amount called-up</td>
            </tr>

            <tr>
                <td width="51pt" height="150"></td>
                <td width="41pt"></td>
                <td width="66pt"></td>
                <td width="55pt"></td>
                <td width="56pt"></td>
                <td width="56pt"></td>
                <td width="31pt">Rs.</td>
                <td width="31pt"></td>
                <td width="31pt"></td>
                <td width="31pt">Rs.</td>
                <td width="31pt"></td>
                <td width="31pt"></td>
            </tr>
        </table>

        <br> <br>

        <table width="100%" style="border:0" autosize="1">
            <tr>
                <td colspan="12" style="border:0"><b><u>List of Ordinary shareholders</u></b> </td>
            </tr>

            <tr>
                <td colspan="12" style="border:0">The names to be arranged in alphabetical order and numbered consecutively</td>
            </tr>

            <tr>
                <td>Consecutive No. </td>
                <td>Register No</td>
                <td>Name of Shareholder </td>
                <td>Address</td>
                <td>Nominal amount of share</td>
                <td>No. of shares held </td>
                <td colspan="3">Amount per share called-up </td>
                <td colspan="3">Total amount called-up</td>
            </tr>

            <tr>
                <td width="49pt" height="200"></td>
                <td width="55pt" ></td>
                <td width="69pt"></td>
                <td width="53pt"></td>
                <td width="53pt"></td>
                <td width="55pt"></td>
                <td width="29pt"></td>
                <td width="29pt"></td>
                <td width="29pt"></td>
                <td width="30pt"></td>
                <td width="29pt"></td>
                <td width="30pt"></td>
            </tr>
        </table>

        <br><br><br><br><br>

        <table width="100%" style="border:0" autosize="1">
            <tr>
                <td colspan="4"  style="border:0"><u><b>Deficiency or Surplus Account</b></u></td>
            <tr>
                <td colspan="4" style="border:0">The period covered by this Account must commence on a date not less than three years before the date of the winding-up order (or the date directed by the official Receiver) or, if the company has not been incorporated for the whole of that period, the date of the formation of the company, unless the Official Receiver otherwise agrees.</td>
            </tr>

            <tr>
                <td colspan="3" width="45%" style="border-bottom:0">Items Contributing to Deficiency (or Reducing Surplus)</td>
                <td width="101pt" align="center" style="border-bottom:0">Rs.</td>
            </tr>
            <tr>
                <td colspan="2" class="td8" style="border-right:0">1.	Excess (if any) of Capital and Liabilities over Assets on the sheet (copy annexed)</td>
                <td align="right" class="td8" style="border-left:0" >as shown by Balance </td>
                <td class="td8"></td>
            </tr>
            <tr>
                <td colspan="3" class="td8">2.	Net dividends and bonuses declared during the period from ___________ to the date of the statements </td>
                <td class="td8"></td>
            </tr>
            <tr>
                <td colspan="3" class="td8">3.	Net trading losses (after charging items shown in note below) for the same period </td>
                <td class="td8"></td>>
            </tr>
            <tr>
                <td colspan="3" class="td8">4.	Losses other than trading losses written off for which provision has been made in the books during the same period (give particulars or annex schedule)</td>
                <td class="td8"></td>
            </tr>
            <tr>
                <td colspan="3" class="td8">5.	Estimated losses now written off for which provision has been made for the purpose of preparing the statement (give particulars or annex schedule)</td>
                <td class="td8"></td>
            </tr>
            <tr>
                <td colspan="3" style="border-top:0">6.	Other items contributing to Deficiency or reducing Surplus:</td>
                <td class="td8"></td>
            </tr>
            <tr>
                <td colspan="2" style="border-bottom:0" width="101pt" >Items Reducing Deficiency (or contributing to Surplus)</td>
                <td style="border-bottom:0"></td>
                <td class="td8"></td>
            </tr>
            <tr>
                <td colspan="2" class="td8" >7.	Excess (if any) of Assets over Capital and Liabilities on the as shown on the Balance Sheet (copy annexed)</td>
                <td class="td8"></td>
                <td class="td8"></td>
            </tr>
            <tr>
                <td colspan="2" class="td8">8.	Net trading profit (after charging items shown in note below) for 		the period from the              to the date of the Statement.</td>
                <td class="td8"></td>
                <td class="td8"></td>
            </tr>
            <tr>
                <td colspan="2" class="td8">9.	Profits and income other than trading profits during the same period (give particular or annex schedule)</td>
                <td class="td8"></td>
                <td class="td8"></td>
            </tr>
            <tr>
                <td colspan="2" class="td8" >10.	Other items reducing Deficiency or contributing to Surplus </td>
                <td class="td8"></td>
                <td class="td8"></td>
            </tr>
            <tr>
                <td colspan="2" class="td8"></td>
                <td style="border-top:0"></td>
                <td style="border-top:0"></td>
            </tr>

            <tr>
                <td align="right" colspan="3" style="border-top:0">Deficiency/Surplus as shown by Statement</td>
                <td style="border-bottom:double"></td>
            </tr>

            <tr>
                <td colspan="2" style="border-bottom:0">Note as to Net Trading Profit and Losses:</td>
                <td style="border-bottom:0" align="center" width="101pt">Rs.</td>
                <td style="border-bottom:0"></td>
            </tr>

            <tr>
                <td colspan="2" class="td8">Particulars are to be inserted here (so far as applicable) of the items mentioned below, which are to be taken into account in arriving at the amount of net trading profits or losses shown in this Account: -</td>
                <td class="td8"></td>
                <td class="td8"></td>
               
            </tr>

            <tr>
                <td colspan="2" class="td8">Provisions for depreciation, renewals, or diminution in value of fixed assets </td>
                <td class="td8"></td>
                <td class="td8"></td>
                
            </tr>

            <tr>
                <td colspan="2" class="td8">Changes Sri Lanka income tax and other Sri Lanka taxation on profits Interest on debenture and other fixed loans </td>
                <td class="td8"></td>
                <td class="td8"></td>
                
            </tr>

            <tr>
                <td colspan="2" class="td8">Payments to directors made by the company and required by law to be   disclosed in the accounts</td>
                <td class="td8"></td>
                <td class="td8"></td>
               
            </tr>

            <tr>
                <td colspan="2" class="td8">Exceptional or non-recurring expenditure:</td>
                <td style="border-top:0"></td>
                <td class="td8"></td>
                
            </tr>

            <tr>
                <td class="td8" style="border-right:0">Less: - Exceptional or non-recurring receipts </td>
                <td width="51pt" class="td8" style="border-left:0">Rs.</td>
                <td class="td8"></td>
                <td class="td8"></td>
            </tr>

            <tr>
                <td class="td8" style="border-right:0">Balance being other trading profits or losses </td>
                <td width="51pt" class="td8" style="border-left:0">Rs.</td>
                <td></td>
                <td class="td8"></td>
            </tr>

            <tr>
                <td style="border-right:0; border-top:0" >Net trading profits or losses as shown in Deficiency or Surplus Account above </td>
                <td width="51pt" style="border-left:0; border-top:0">Rs.</td>
                <td></td>
                <td style="border-top:0"></td>
            </tr>

        </table>

        <br>

        <table width="100%" style="border:0" autosize="1">
            <tr>
                <td style="border:0"><b>Note</b></td>
            </tr>

            <tr>
                <td style="border:0">(1) This should be submitted within 14 days from the relevant date – Please see sec.283(3)</td>
            </tr>

            <tr>
                <td style="border:0">(2) This is to be verified by an affidavit as required by Section 283 (1) </td>
            </tr>
        </table>


    </section>
    </body>
</html>