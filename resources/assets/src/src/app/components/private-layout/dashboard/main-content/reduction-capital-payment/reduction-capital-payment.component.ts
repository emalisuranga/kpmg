import { Router } from '@angular/router';
import { IBuyDetails } from './../../../../../storage/ibuy-details';
import { Item, IBuy } from './../../../../../http/models/payment';
import { AuthService } from './../../../../../http/shared/auth.service';
import { CalculationService } from './../../../../../http/shared/calculation.service';
import { Component, OnInit } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { DataService } from '../../../../../storage/data.service';
import { PaymentService } from '../../../../../http/services/payment.service';
import { environment } from '../../../../../../environments/environment';
import { GeneralService } from 'src/app/http/services/general.service';

@Component({
  selector: 'app-reduction-capital-payment',
  templateUrl: './reduction-capital-payment.component.html',
  styleUrls: ['./reduction-capital-payment.component.scss']
})
export class ReductionCapitalPaymentComponent implements OnInit {
  subtotalPrice: number;
  taxPrice: number;
  totalPrice: number;
  cipher_message: string;
  paymentGateway: string = environment.paymentGateway;
  public MaxAmount: number;
  public CompanyId: string;

  penalty = 0;
  pub_penalty = 0;
  not_pub_penalty = 0;

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

   // if (localStorage.getItem('PAYMENT_FOR_STATED_CAPITAL') === '"yes"' && localStorage.getItem('StatedCapitalCompanyId')) {
      this.CompanyId = localStorage.getItem('StatedCapitalCompanyId');
     // console.log( this.iBy.getModuleId);
   // }


    this.isPassedReslutionDate();
  }

  private isPassedReslutionDate() {
    const data = {
      company_id: this.CompanyId,
      type: this.iBy.getModuleType,
      request_id: this.iBy.getModuleId
    };

    this.general.reductionPenalty(data)
      .subscribe(
        req => {
          console.log(req);
          this.penalty = parseFloat(req['penalty']);
          this.pub_penalty = parseFloat(req['pub_penalty']);
          this.not_pub_penalty = parseFloat(req['not_pub_penalty']);
          this.getCipherToken();
        }
      );

  }


  getCipherToken() {
    if (this.iBy.getModuleId === null) { return this.route.navigate(['dashboard/home']); }

   let module_id =  this.iBy.getModuleId;

    const buy: IBuy = {
      module_type: this.iBy.getModuleType,
    //  module_id: this.iBy.getModuleId,
      module_id: module_id,
      description: this.iBy.getDescription,
      item: this.iBy.getItem,
      extraPay: null,
      penalty: (this.penalty +  this.pub_penalty + this.not_pub_penalty ).toString()
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

