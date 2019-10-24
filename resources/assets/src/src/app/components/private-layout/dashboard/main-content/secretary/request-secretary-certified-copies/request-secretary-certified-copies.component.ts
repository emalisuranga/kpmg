import { Component, OnInit, AfterViewInit, ElementRef } from '@angular/core';
import { DataService } from '../../../../../../storage/data.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { NgxSpinnerService } from 'ngx-spinner';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { Item, IBuy } from '../../../../../../http/models/payment';
import { Router, ActivatedRoute } from '@angular/router';
import { CalculationService } from './../../../../../../http/shared/calculation.service';
import { SecretaryCertifiedCopiesService } from '../secretary-certified-copies.service';
import { SecretaryService } from '../../../../../../http/services/secretary.service';
import { environment } from '../../../../../../../environments/environment';
import { ISecretaryCertifiedCopy } from '../../../../../../http/models/seccertifate.model';


@Component({
  selector: 'app-request-secretary-certified-copies',
  templateUrl: './request-secretary-certified-copies.component.html',
  styleUrls: ['./request-secretary-certified-copies.component.scss']
})
export class RequestSecretaryCertifiedCopiesComponent implements OnInit {

  companyId: string;

  secCerReqId: number;
  secId: number;
  nic: string;
  fname: string;
  lname: string;
  cnum: string;
  type: string;
  regnum: string;
  name: string;
  validationMessage = '';
  enableStep1Submission = true;
  preSecCerRequest = [];
  progress = {

    stepArr: [
      { label: 'Amount of certified copies', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },

    ],

    progressPercentage: '25%'

  };

  cipher_message: string;
  stepOn = 0;
  email = ' ';
  blockBackToForm = false;
  blockPayment = false;
  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;

  secCertified: ISecretaryCertifiedCopy = { quantity: null };

  constructor(private router: Router, public calculation: CalculationService, private crToken: PaymentService, private helper: HelperService, private spinner: NgxSpinnerService, private httpClient: HttpClient, public data: DataService, private general: GeneralService, private SecCerData: SecretaryCertifiedCopiesService, private secretaryService: SecretaryService, ) { }

  ngOnInit() {
    this.loadSecCerRequest();
    this.secId = this.SecCerData.getSecId;
    this.nic = this.SecCerData.getNic;
    this.fname = this.SecCerData.getFname;
    this.lname = this.SecCerData.getLname;
    this.cnum = this.SecCerData.getCnum;
    this.type = this.SecCerData.getSecType;
    this.name = this.SecCerData.getName;
    this.regnum = this.SecCerData.getRnum;
  }

  changeProgressStatuses(newStatus = 1) {
    this.stepOn = newStatus;
    this.progress.progressPercentage = (this.stepOn >= 2) ? (25 * 2 + this.stepOn * 50) + '%' : (25 + this.stepOn * 50) + '%';
    for (let i = 0; i < this.progress['stepArr'].length; i++) {
      if (this.stepOn > i) {
        this.progress['stepArr'][i]['status'] = 'activated';
      } else if (this.stepOn === i) {
        this.progress['stepArr'][i]['status'] = 'active';
      } else {
        this.progress['stepArr'][i]['status'] = '';
      }
    }
    return this.progress;
  }

  getEmail() {

    this.email = localStorage.getItem('currentUser');
    this.email = this.email.replace(/^"(.*)"$/, '$1');
    return this.email;
  }

  validation() {
    if (!
      (
        this.secCertified.quantity && Number(this.secCertified.quantity)
      )
    ) {
      this.validationMessage = 'Please fill above required field correctly denoted by asterik(*)';
      this.enableStep1Submission = false;
      return false;
    } else {
      this.validationMessage = '';
      this.enableStep1Submission = true;
      return true;
    }
  }

  // Load secretary certificate request on beganing...
  loadSecCerRequest() {

    const data = {
      secID: this.SecCerData.getSecId,
      type: this.SecCerData.getSecType
    };
    this.secretaryService.loadSecCerRequest(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['preSecCerRequest']) {
              this.preSecCerRequest = [];

              const data1 = {
                id: req['preSecCerRequest'][0]['id'],
                secretaryID: req['preSecCerRequest'][0]['secretary_id'],
                secretaryType: 'induvidual',
                status: req['preSecCerRequest'][0]['status'],
                noOfCopies: req['preSecCerRequest'][0]['no_of_copies'],
              };
              this.preSecCerRequest.push(data1);
              this.secCertified.quantity = req['preSecCerRequest'][0]['no_of_copies'];
              this.SecCerData.setReqID(req['preSecCerRequest'][0]['id']);
              this.SecCerData.setStatus(req['preSecCerRequest'][0]['status']);
              console.log(this.preSecCerRequest);
            }
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  // Details submit...
  detailsSubmit() {
    if (this.validation()) {
      const data = {
        reqID: this.SecCerData.getReqID,
        quantity: this.secCertified.quantity,
        secID: this.SecCerData.getSecId,
        email: this.getEmail(),
        type: this.SecCerData.getSecType
      };
      this.secretaryService.secretaryCertificateRequest(data)
        .subscribe(
          req => {
            if (req['status']) {
              this.blockBackToForm = false;
              this.changeProgressStatuses(1);
              this.SecCerData.setReqID(req['secCerReqId']);
            }
          },
          error => {
            console.log(error);
          }
        );
    } else {
      this.validationMessage = 'Please fill above required field correctly denoted by asterik(*)';
      this.blockBackToForm = false;
    }

  }

  getCipherToken() {
    if (!this.SecCerData.getReqID) { return this.router.navigate(['dashboard/home']); }

    const item: Array<Item> = [{
      fee_type: 'PAYMENT_ISSUING_CERTIFIED_COPIES_OF_SECRETARIES',
      description: 'For secretary certified copies Issue.',
      quantity: this.secCertified.quantity,
    }];

    const buy: IBuy = {
      module_type: 'MODULE_ISSUING_CERTIFIED_COPIES_OF_SECRETARIES',
      module_id: this.SecCerData.getReqID.toString(),
      description: 'For secretary certified copies Issue',
      item: item,
      extraPay: null
    };

    this.crToken.getCrToken(buy).subscribe(
      req => {
        this.cipher_message = req.token;
        this.blockPayment = true;

      },
      error => { console.log(error); }
    );
  }

  areYouSurePayYes() {
    this.blockPayment = true;
  }
  areYouSurePayNo() {
    this.blockPayment = false;
  }

  areYouSureYes() {
    this.blockBackToForm = true;
  }
  areYouSureNo() {
    this.blockBackToForm = false;
  }

}
