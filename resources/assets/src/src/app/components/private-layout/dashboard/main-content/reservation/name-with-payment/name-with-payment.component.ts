import { Router } from '@angular/router';
import { IBuyDetails } from './../../../../../../storage/ibuy-details';
import { Item, IBuy } from './../../../../../../http/models/payment';
import { AuthService } from './../../../../../../http/shared/auth.service';
import { CalculationService } from './../../../../../../http/shared/calculation.service';
import { Component, OnInit } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { DataService } from '../../../../../../storage/data.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { environment } from '../../../../../../../environments/environment';
import { GeneralService } from 'src/app/http/services/general.service';

@Component({
  selector: 'app-name-with-payment',
  templateUrl: './name-with-payment.component.html',
  styleUrls: ['./name-with-payment.component.scss']
})
export class NameWithPaymentComponent implements OnInit {
  subtotalPrice: number;
  taxPrice: number;
  totalPrice: number;
  cipher_message: string;
  paymentGateway: string = environment.paymentGateway;
  public MaxAmount: number;
  public CompanyId: string;

  isExeedResDate = false;
  penalty = 0;
  totalAmt = 0;
  taxCharges = 0;
  convenienceCharge = 0;

  constructor(
    private snotifyService: ToastrService,
    private auth: AuthService,
    private route: Router,
    public data: DataService,
    public calculation: CalculationService,
    private crToken: PaymentService,
    public iBy: IBuyDetails,
    public general: GeneralService
  ) {
   // this.getCipherToken();
  }

  ngOnInit() {

  //  if (localStorage.getItem('nameChangeCompanyId')) {
      this.CompanyId = localStorage.getItem('nameChangeCompanyId');
  //  }

    this.isPassedReslutionDate();
  }

  private isPassedReslutionDate() {
    const data = {
      company_id: this.CompanyId,
      type: this.iBy.getModuleType,
      request_id: this.iBy.getModuleId
    };

    this.general.isResolutionDateExeed(data)
      .subscribe(
        req => {
          console.log(req);
         // this.isExeedResDate = req['status'];
         this.penalty = parseFloat(req['penaly_charge']);
      //   this.totalAmt =  this.penalty +
       //                   (this.penalty * 0.15) +
       //                   (this.penalty + this.penalty * 0.15) * 0.02;
         let totalGross = 0;
        for ( let i in this.iBy.getItem) {
            totalGross = totalGross + parseFloat( this.calculation.getValue(this.iBy.getItem[i].fee_type, this.iBy.getItem[i].quantity , 0 , false , '').toString() );
        }
        let totalGrossWithPenalty = totalGross + this.penalty;
        this.taxCharges = totalGrossWithPenalty * 0.15;

        this.convenienceCharge = ( totalGrossWithPenalty + this.taxCharges ) * 0.02;

        this.totalAmt = totalGrossWithPenalty + this.taxCharges + this.convenienceCharge;

          this.getCipherToken();
        }
      );

  }

  private removePenalyLimitedDate() {
     let iByUpdate = [];
     for ( let i in this.iBy.getItem ) {
          if (this.iBy.getItem[i]['fee_type'] === 'PAYMENT_PENALTY_LIMITED_DATE') {
              continue;
          }
          iByUpdate.push(this.iBy.getItem[i]);
     }
     return iByUpdate;
  }


  getCipherToken() {
  //  this.isPassedReslutionDate();
    if (this.iBy.getModuleId === null) { return this.route.navigate(['dashboard/home']); }

   let module_id =  this.iBy.getModuleId;

   if (this.iBy.getModuleType === 'MODULE_NAME_CHANGE') {
    module_id = localStorage.getItem('nameChangeCompanyId');
   }

    const buy: IBuy = {
      module_type: this.iBy.getModuleType,
    //  module_id: this.iBy.getModuleId,
      module_id: module_id,
      description: this.iBy.getDescription,
     // item: this.iBy.getItem,
      item: this.removePenalyLimitedDate(),
      extraPay: this.iBy.getExtraPayment,
     // penalty: this.iBy.getExtraPayment,
     penalty: (this.penalty) ? this.penalty.toString() : null,
    };

    this.crToken.getCrToken(buy).subscribe(
      req => {
        this.cipher_message = req.token;
        this.iBy.clean();
        this.data.outLocalData('ResName');
      },
      error => { this.snotifyService.error(error, 'error'); }
    );
  }

}
