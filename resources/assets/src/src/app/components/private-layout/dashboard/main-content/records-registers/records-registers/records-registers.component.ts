import { Component, OnInit, AfterViewInit } from '@angular/core';
import { DataService } from '../../../../../../storage/data.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { IMrOldAddressData, IMrAddressData, ISrOldAddressData, ISrAddressData, IRcOldAddressData, IRcAddressData } from '../../../../../../http/models/address.model';
import { RrAddressChangeService } from '../../../../../../http/services/rr-address-change.service';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { NgxSpinnerService } from 'ngx-spinner';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { Item, IBuy } from '../../../../../../http/models/payment';
import { Router, ActivatedRoute } from '@angular/router';
import { CalculationService } from '.././../../../../../http/shared/calculation.service';
import * as $ from 'jquery';
import { environment } from '../../../../../../../environments/environment';

@Component({
  selector: 'app-records-registers',
  templateUrl: './records-registers.component.html',
  styleUrls: ['./records-registers.component.scss']
})
export class RecordsRegistersComponent implements OnInit, AfterViewInit {

  progress = {

    stepArr: [
      { label: 'Records/Registers Address Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },

    ],

    progressPercentage: '12.5%'

  };

  companyId: string;
  changeId: string;
  oldaddressId: string;
  companyName: string;
  companyRegno: string;
  postfix: string;
  members: any;
  signbyid: any;
  convert: any;
  requestId: string;
  enableGoToDownload = false;
  effectiveDate: string;
  blockBackToForm = false;
  blockPayment = false;
  enableGoToPay = false;
  storage1: any;
  storage2: any;
  itemcount: any;
  application = [];
  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;
  addressValidationMessage: any;
  date = new Date();
  email = '';
  stepOn = 0;
  cipher_message: string;
  penaltyvalue: any;
  penalty_charge: any;

  validAddress = false;
  validMAddress = false;
  validSAddress = false;

  regAddress = [];

  addresses = [];
  oldaddresses = [];
  deladdresses = [];

  Saddresses = [];
  Soldaddresses = [];
  Sdeladdresses = [];

  Maddresses = [];
  Moldaddresses = [];
  Mdeladdresses = [];

  address: IRcAddressData = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, discription: null };
  oldaddress: IRcOldAddressData = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, discription: null };

  maddress: IMrAddressData = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, discription: null };
  moldaddress: IMrOldAddressData = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, discription: null };

  saddress: ISrAddressData = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, discription: null };
  soldaddress: ISrOldAddressData = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, discription: null };

  enableStep1Submission: boolean;
  enableStep2SubmissionEdit = false;
  addlist = [];
  remlist = [];
  extra = [];

  province: string;
  district: string;
  city: string;
  gnDivision: string;
  id: any;

  memberRegister: boolean;
  document_confirm = false;

  caseId: string;
  court_status = '';
  court_name = '';
  court_case_no = '';
  court_date = '';
  court_penalty = '';
  court_period = '';
  court_discharged = '';
  validateCourtSectionFlag = false;

  formattedTodayValue = '';
  incoDate = '';
  description: string;
  dataloaddone: any;

  constructor(private router: Router,
    public calculation: CalculationService,
     private crToken: PaymentService,
    private helper: HelperService,
     private spinner: NgxSpinnerService,
      private httpClient: HttpClient,
       public data: DataService,
        private general: GeneralService,
         private rrAddressChangeService: RrAddressChangeService) {
          if (JSON.parse(localStorage.getItem('RRstorage'))) {
            this.storage2 = JSON.parse(localStorage.getItem('RRstorage'));
            if (this.storage2['comId'] === JSON.parse(localStorage.getItem('RRcompanyId')) && JSON.parse(localStorage.getItem('RRstatus')) === 'processing') {
              this.storage1 = JSON.parse(localStorage.getItem('RRstorage'));
              this.companyId = this.storage1['comId'];
              this.requestId = this.storage1['changeReqId'];
              this.memberRegister = this.storage1['comType'];
              console.log(this.companyId);
              console.log(this.requestId);
               // this.loadUploadedFile();
               this.loadCompanyAddressProcessing();
             //  this.changeProgressStatuses(2);
            }
            else {
              this.companyId = JSON.parse(localStorage.getItem('RRcompanyId'));
              this.memberRegister = JSON.parse(localStorage.getItem('RRtype'));
              console.log(this.companyId);
               this.loadCompanyAddress();
            }
          }
          else {
            this.companyId = JSON.parse(localStorage.getItem('RRcompanyId'));
            this.memberRegister = JSON.parse(localStorage.getItem('RRtype'));
            console.log(this.companyId);
            this.loadCompanyAddress();
          }
          }

  ngOnInit() {
    this.formattedTodayValue = this.getFormatedToday();
  }

  ngAfterViewInit() {

    $(document).on('click', '.record-handler-remove', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      self.parent().parent().remove();
    });

    $('button.add-director').on('click', function () {
      $('#director-modal .close-modal-item').trigger('click');
    });

    $('button.add-sec').on('click', function () {
      $('#sec-modal .close-modal-item').trigger('click');
    });

    $('button.add-tre').on('click', function () {
      $('#tre-modal .close-modal-item').trigger('click');
    });

    $('button.add-addit').on('click', function () {
      $('#addit-modal .close-modal-item').trigger('click');
    });

    $('button.add-memb').on('click', function () {
      $('#memb-modal .close-modal-item').trigger('click');
    });

    $('.stakeholder-type-tab-wrapper .tab').on('click', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      $('.stakeholder-type-tab-wrapper .tab').removeClass('active');
      $(this).addClass('active');

    });


  }

  private getFormatedToday() {
    var d = new Date(),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) {
      month = '0' + month;
    }
    if (day.length < 2) {
      day = '0' + day;
    }

    return [year, month, day].join('-').toString();
}

  changeProgressStatuses(newStatus = 1) {
    this.stepOn = newStatus;
    this.progress.progressPercentage = (this.stepOn >= 4) ? (12.5 * 2 + this.stepOn * 25) + '%' : (12.5 + this.stepOn * 25) + '%';

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

  loadCompanyAddress() {
    const data = {
      id: this.companyId,
      type: 'submit',
      comType: this.memberRegister,
      email: this.getEmail()
    };
    this.rrAddressChangeService.loadCompanyAddress(data)
      .subscribe(
        req => {
          if (req['data']) {
            if (req['data']['address']){
                const data1 = {
                  id: req['data']['address']['oid'],
                  province: req['data']['address']['province'],
                  district: req['data']['address']['district'],
                  city: req['data']['address']['city'],
                  gnDivision: req['data']['address']['gnDivision'],
                  localAddress1: req['data']['address']['address1'],
                  localAddress2: req['data']['address']['address2'],
                  postcode: req['data']['address']['postcode'],
                };
                this.regAddress.push(data1);
            }
            if (req['data']['raddress']){
              for (let i in req['data']['raddress']){
                const data1 = {
                  id: req['data']['raddress'][i]['oid'],
                  showEditPaneForPresident: false,
                  province: req['data']['raddress'][i]['province'],
                  district: req['data']['raddress'][i]['district'],
                  city: req['data']['raddress'][i]['city'],
                  gnDivision: req['data']['raddress'][i]['gnDivision'],
                  localAddress1: req['data']['raddress'][i]['address1'],
                  localAddress2: req['data']['raddress'][i]['address2'],
                  postcode: req['data']['raddress'][i]['postcode'],
                  date: req['data']['raddress'][i]['date'],
                  discription: req['data']['raddress'][i]['description'],
                };
                this.oldaddresses.push(data1);
              }
            }
            if (this.memberRegister === true) {
              if (req['data']['maddress']){
                for (let i in req['data']['maddress']){
                  const data1 = {
                    id: req['data']['maddress'][i]['oid'],
                    showEditPaneForPresident: false,
                    province: req['data']['maddress'][i]['province'],
                    district: req['data']['maddress'][i]['district'],
                    city: req['data']['maddress'][i]['city'],
                    gnDivision: req['data']['maddress'][i]['gnDivision'],
                    localAddress1: req['data']['maddress'][i]['address1'],
                    localAddress2: req['data']['maddress'][i]['address2'],
                    postcode: req['data']['maddress'][i]['postcode'],
                    date: req['data']['maddress'][i]['date'],
                    discription: req['data']['maddress'][i]['description'],
                  };
                  this.Moldaddresses.push(data1);
                }
              }

            }
            if (this.memberRegister === false) {
              if (req['data']['saddress']){
                for (let i in req['data']['saddress']){
                  const data1 = {
                    id: req['data']['saddress'][i]['oid'],
                    showEditPaneForPresident: false,
                    province: req['data']['saddress'][i]['province'],
                    district: req['data']['saddress'][i]['district'],
                    city: req['data']['saddress'][i]['city'],
                    gnDivision: req['data']['saddress'][i]['gnDivision'],
                    localAddress1: req['data']['saddress'][i]['address1'],
                    localAddress2: req['data']['saddress'][i]['address2'],
                    postcode: req['data']['saddress'][i]['postcode'],
                    date: req['data']['saddress'][i]['date'],
                    discription: req['data']['saddress'][i]['description'],
                  };
                  this.Soldaddresses.push(data1);
                }
              }

            }
            console.log(this.Soldaddresses);
            console.log(this.Moldaddresses);
            this.companyName = req['data']['company'][0]['name'];
            this.companyRegno = req['data']['company'][0]['registration_no'];
            this.postfix = req['data']['company'][0]['postfix'];
            this.incoDate = req['data']['company'][0]['incorporation_at'];
            this.members = req['data']['members'];

          }
          if (req['message'] === 'Unauthorized user is trying a company change') {
            alert('Unauthorized user is trying a company records registers change');
            this.router.navigate(['/dashboard/home']);
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  validateCourtSection(){

    // && this.court_penalty !== '' && parseFloat(this.court_penalty) > 0


    if (this.court_status === 'yes') {

      this.validateCourtSectionFlag = ( this.court_name !== '' && this.court_case_no !== '' && this.court_date !== '' );
      console.log('111');
      console.log(this.validateCourtSectionFlag);
      return true;
    }
    if ( this.court_status === 'no') {
      console.log('2222');

      this.validateCourtSectionFlag =  true;
      console.log(this.validateCourtSectionFlag);
      return true;
    }
    console.log('3333');
    this.validateCourtSectionFlag =  false;


}

  courtdataSubmit() {


    const data = {
      type: 'submit',
      reqid: this.requestId,
      caseId: this.caseId,
      id: this.companyId,
      court_status: this.court_status,
      court_name: this.court_name,
      court_date: this.court_date,
      court_case_no: this.court_case_no,
      court_penalty: this.court_penalty,
      court_period: this.court_period,
      court_discharged: this.court_discharged

    };

    this.rrAddressChangeService.rrAddressCourtDataSubmit(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.caseId = req['caseid'];
            if (this.court_status === 'no') {
              this.penaltyvalue = req['penaly_charge'];
              this.court_name = '';
              this.court_date = '';
              this.court_case_no = '';
              this.court_penalty = '';
              this.court_period = '';
              this.court_discharged = '';
            }
            else{
              this.penaltyvalue = 0;
            }
          }
          this.changeProgressStatuses(3);
        },
        error => {
          console.log(error);
        }
      );

  }

  changeStakeholers(id, ty, action = '') {
    const data = {
      companyId: this.companyId,
      reqid: this.requestId,
      email: this.getEmail(),
      records: this.oldaddresses,
      shares: this.Soldaddresses,
      members: this.Moldaddresses,
      signby: this.signbyid,
      type: ty,
      id: id,
      action: action
    };
    this.spinner.show();
    this.rrAddressChangeService.rrAddressChange(data)
      .subscribe(
        req => {
          this.requestId = req['reqID'];
         // this.loadCompanyAddressProcessing();
          // this.goToNext();
        },
        error => {
          console.log(error);
          this.spinner.hide();
        },
        () => {
          this.loadCompanyAddressProcessing();
         // console.log(error);
        }
      );
  }

  goToNext(){
    console.log(this.addresses);
    console.log(this.Saddresses);
    console.log(this.Maddresses);
    let x = 0;
    let y = 0;
    let z = 0;
    let a = 0;
    let b = 0;
    let c = 0;
    let d = 0;
    let e = 0;
    let f = 0;
    let p = this.addresses.length;
    let r = this.Saddresses.length;
    let q = this.Maddresses.length;

    // counting changes
    console.log(this.oldaddresses);
    console.log(this.Soldaddresses);
    console.log(this.Moldaddresses);
    for (let director of this.oldaddresses) {
      if (director.newid) {
        a = a + 1;
      }
      else {
        continue;
      }
    }
    for (let sec of this.Soldaddresses) {
      if (sec.newid) {
        b = b + 1;
      }
      else {
        continue;
      }
    }
    for (let sec of this.Moldaddresses) {
      if (sec.newid) {
        c = c + 1;
      }
      else {
        continue;
      }
    }
    // end of counting changes

    for (let director of this.oldaddresses) {
      if (director.isdeleted === null ) {
        x = x + 1;
      }
      else {
        d = d + 1;
        continue;
      }
    }
    for (let sec of this.Soldaddresses) {
      if (sec.isdeleted === null ) {
        y = y + 1;
      }
      else {
        e = e + 1;
        continue;
      }
    }
    for (let sec of this.Moldaddresses) {
      if (sec.isdeleted === null ) {
        z = z + 1;
      }
      else {
        f = f + 1;
        continue;
      }
    }
    let changes = a + b + c;
    let deletes = d + e + f;
    let newadds = p + q + r;
    // let dirTotal = x + p;
    // let secTotal = y + r + q + z;
    if (changes > 0 || newadds > 0 || deletes > 0) {
          this.enableGoToDownload = true;
            console.log('ok');
        }
    else{
          this.enableGoToDownload = false;
          console.log('notok');
        }
  }

  dataSubmit(action = '') {


    const data = {
      type: 'submit',
      reqid: this.requestId,
      id: this.companyId,
      addArr: this.addresses,
      addSArr: this.Saddresses,
      addMArr: this.Maddresses,
      email: this.getEmail(),
      signby: this.signbyid,
      memtype: this.memberRegister

    };
    this.spinner.show();
    this.rrAddressChangeService.rrAddressDataSubmit(data)
      .subscribe(
        req => {
          if (action === 'next') {
            this.changeProgressStatuses(1);
            this.blockBackToForm = false;
          }
          if (req['status']) {


          }
          this.requestId = req['reqID'];
         // this.loadCompanyAddressProcessing();
          this.penalty_charge = req['penalty_value'];
          // this.loadCompanyAddressProcessing();
          // setTimeout( () => {
          //   if (this.dataloaddone) {
          //     console.log('going thru');
          //     if (!this.penalty_charge){
          //       console.log('going thru thru');
          //       // this.penaltyvalue = req['penaly_charge'];
          //         this.court_status = 'no';
          //         this.caseId = null;
          //         this.court_name = '';
          //         this.court_date = '';
          //         this.court_case_no = '';
          //         this.court_penalty = '';
          //         this.court_period = '';
          //         this.court_discharged = '';
          //     }
          //   }
          // }, 2000);
         // this.f(this.dataloaddone, this.penalty_charge);
          // if (this.dataloaddone) {
          //   console.log('going thru');
          //   if (!this.penalty_charge){
          //     console.log('going thru thru');
          //     // this.penaltyvalue = req['penaly_charge'];
          //       this.court_status = 'no';
          //       this.caseId = null;
          //       this.court_name = '';
          //       this.court_date = '';
          //       this.court_case_no = '';
          //       this.court_penalty = '';
          //       this.court_period = '';
          //       this.court_discharged = '';
          //   }

          // }
          // this.memberload();
        },
        error => {
          console.log(error);
          this.spinner.hide();
        },
        () => {
          this.loadCompanyAddressProcessing();
          this.loadUploadedFile();
          this.validateCourtSection();
          setTimeout( () => {
            if (this.dataloaddone) {
              console.log('going thru');
              if (!this.penalty_charge){
                console.log('going thru thru');
                // this.penaltyvalue = req['penaly_charge'];
                  this.court_status = 'no';
                  this.caseId = null;
                  this.court_name = '';
                  this.court_date = '';
                  this.court_case_no = '';
                  this.court_penalty = '';
                  this.court_period = '';
                  this.court_discharged = '';
              }
            }
          }, 3000);
        }
      );

  }

  async f(loaddone, penalty) {

    let ld = await loaddone;
    let pen = await penalty; // wait till the promise resolves (*)
    console.log('going thru');

    if (ld) {
      console.log('going thru thru');
      if (!pen){
        // this.penaltyvalue = req['penaly_charge'];
          this.court_status = 'no';
          this.caseId = null;
          this.court_name = '';
          this.court_date = '';
          this.court_case_no = '';
          this.court_penalty = '';
          this.court_period = '';
          this.court_discharged = '';
      }

    }
  }

  checkDoubleRecord(){
    for ( var i = 0; i < this.deladdresses.length; i++) {
      for ( var j = 0; j < this.oldaddresses.length; j++) {
        if (this.oldaddresses[j]['id'] === this.deladdresses[i]) {
          this.oldaddresses[j]['showEditPaneForPresident'] = false;
        }
     }
   }

  }

  checkDoubleShare(){
    for ( var i = 0; i < this.Sdeladdresses.length; i++) {
      for ( var j = 0; j < this.Soldaddresses.length; j++) {
        if (this.Soldaddresses[j]['id'] === this.Sdeladdresses[i]) {
          this.Soldaddresses[j]['showEditPaneForPresident'] = false;
        }
     }
   }

  }

  checkDoubleMember(){
    for ( var i = 0; i < this.Mdeladdresses.length; i++) {
      for ( var j = 0; j < this.Moldaddresses.length; j++) {
        if (this.Moldaddresses[j]['id'] === this.Mdeladdresses[i]) {
          this.Moldaddresses[j]['showEditPaneForPresident'] = false;
        }
     }
   }

  }

  loadCompanyAddressProcessing() {
    const data = {
      id: this.companyId,
      type: 'processing',
      requestID: this.requestId,
      comType: this.memberRegister,
      email: this.getEmail()
    };
    this.spinner.show();
    this.rrAddressChangeService.loadCompanyAddress(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.regAddress = [];
            this.addresses = [];
            this.oldaddresses = [];
            this.Saddresses = [];
            this.Soldaddresses = [];
            this.Maddresses = [];
            this.Moldaddresses = [];
            if (req['data']['address']){
              const data1 = {
                id: req['data']['address']['oid'],
                province: req['data']['address']['province'],
                district: req['data']['address']['district'],
                city: req['data']['address']['city'],
                gnDivision: req['data']['address']['gnDivision'],
                localAddress1: req['data']['address']['address1'],
                localAddress2: req['data']['address']['address2'],
                postcode: req['data']['address']['postcode'],
              };
              this.regAddress.push(data1);
          }
          if (req['data']['oldrecaddresses']){
            this.oldaddresses = req['data']['oldrecaddresses'];
          }
              if (req['data']['addresspending']) {
              for (let i in req['data']['addresspending']) {
                  const data1 = {
                    id: req['data']['addresspending'][i]['oid'],
                    showEditPaneForPresident: false,
                    province: req['data']['addresspending'][i]['province'],
                    district: req['data']['addresspending'][i]['district'],
                    city: req['data']['addresspending'][i]['city'],
                    gnDivision: req['data']['addresspending'][i]['gnDivision'],
                    localAddress1: req['data']['addresspending'][i]['address1'],
                    localAddress2: req['data']['addresspending'][i]['address2'],
                    postcode: req['data']['addresspending'][i]['postcode'],
                    date: req['data']['addresspending'][i]['date'],
                    type: req['data']['addresspending'][i]['type'],
                    discription: req['data']['addresspending'][i]['description'],
                  };
                  this.addresses.push(data1);
            }
            }
            if (req['data']['oldshareaddresses']){
              this.Soldaddresses = req['data']['oldshareaddresses'];
            }
              if (req['data']['shareaddresspending']) {
              for (let i in req['data']['shareaddresspending']) {
                  const data1 = {
                    id: req['data']['shareaddresspending'][i]['oid'],
                    showEditPaneForPresident: false,
                    province: req['data']['shareaddresspending'][i]['province'],
                    district: req['data']['shareaddresspending'][i]['district'],
                    city: req['data']['shareaddresspending'][i]['city'],
                    gnDivision: req['data']['shareaddresspending'][i]['gnDivision'],
                    localAddress1: req['data']['shareaddresspending'][i]['address1'],
                    localAddress2: req['data']['shareaddresspending'][i]['address2'],
                    postcode: req['data']['shareaddresspending'][i]['postcode'],
                    date: req['data']['shareaddresspending'][i]['date'],
                    type: req['data']['shareaddresspending'][i]['type'],
                    discription: req['data']['shareaddresspending'][i]['description'],
                  };
                  this.Saddresses.push(data1);
            }
            }
            if (req['data']['oldmemberaddresses']){
              this.Moldaddresses = req['data']['oldmemberaddresses'];
            }
              if (req['data']['memberaddresspending']) {
              for (let i in req['data']['memberaddresspending']) {
                  const data1 = {
                    id: req['data']['memberaddresspending'][i]['oid'],
                    showEditPaneForPresident: false,
                    province: req['data']['memberaddresspending'][i]['province'],
                    district: req['data']['memberaddresspending'][i]['district'],
                    city: req['data']['memberaddresspending'][i]['city'],
                    gnDivision: req['data']['memberaddresspending'][i]['gnDivision'],
                    localAddress1: req['data']['memberaddresspending'][i]['address1'],
                    localAddress2: req['data']['memberaddresspending'][i]['address2'],
                    postcode: req['data']['memberaddresspending'][i]['postcode'],
                    date: req['data']['memberaddresspending'][i]['date'],
                    type: req['data']['memberaddresspending'][i]['type'],
                    discription: req['data']['memberaddresspending'][i]['description'],
                  };
                  this.Maddresses.push(data1);
            }
            }
            if (req['data']['case']) {
              this.caseId = req['data']['case']['id'];
              this.court_status = req['data']['case']['court_status'];
              this.court_name = req['data']['case']['court_name'];
              this.court_case_no = req['data']['case']['court_case_no'];
              this.court_date = req['data']['case']['court_date'];
              this.court_penalty = req['data']['case']['court_penalty'];
              this.court_period = req['data']['case']['court_period'];
              this.court_discharged = req['data']['case']['court_discharged'];
            }
            if (!req['data']['case']) {
              this.court_status = 'no';
            }
            this.companyName = req['data']['company'][0]['name'];
            this.companyRegno = req['data']['company'][0]['registration_no'];
            this.postfix = req['data']['company'][0]['postfix'];
            this.incoDate = req['data']['company'][0]['incorporation_at'];
            this.members = req['data']['members'];
            if (req['data']['signedbytype'] === 'COMPANY_MEMBERS') {
              this.convert = req['data']['signedby'];
              this.signbyid = this.convert.toString() + '-' + 0;

            }
            else if (req['data']['signedbytype'] === 'COMPANY_MEMBER_FIRMS') {
              this.convert = req['data']['signedby'];
              this.signbyid = this.convert.toString() + '-' + 1;

            }
            // this.convert = req['data']['signedby'];
            // this.signbyid = this.convert.toString();
           // setTimeout( () => { this.goToNext(); }, 1500);
           console.log('precomplete');
          }
         // this.addressValidationStep1();
        },
        error => {
          console.log(error);
          this.spinner.hide();
        },
        () => {
          this.goToNext();
          this.validateCourtSection();
          this.dataloaddone = true;
          console.log('postcomplete');
          this.spinner.hide();
         // console.log(error);
        }
      );
  }

  // download functions


  form14Download() {
    const data = {

      comId: this.companyId,
      email: this.getEmail(),
      requestID: this.requestId,
      comType: this.memberRegister,

    };

    this.rrAddressChangeService.getPDFService(data).subscribe(
      response => {

        this.helper.download(response);
      },
      error => {
        console.log(error);
      }
    );

  }

  // for uplaod secretary pdf files...
  fileUpload(event, description, docType) {

    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      let file: File = fileList[0];
      let fileSize = fileList[0].size;
      let filetype = fileList[0].type;
      if (fileSize > 1024 * 1024 * 4) { // 4mb restriction
        alert('File size should be less than 4 MB');
        return false;
      }
      if (!filetype.match('application/pdf')) {
        alert('Please upload pdf files only');
        return false;
      }

      let formData: FormData = new FormData();

      formData.append('uploadFile', file, file.name);
      formData.append('docType', docType);
      formData.append('comId', this.companyId);
      formData.append('requestId', this.requestId);
      formData.append('description', description);
      // formData.append('changeid', JSON.parse(localStorage.getItem('changeid')));
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getRRFileUploadUrl();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            const datas = {
              id: data['docid'],
              name: data['name'],
              token: data['token'],
              pdfname: data['pdfname'],
              file_description: data['file_description'],
            };
            if (docType === 'applicationUpload') {
              this.application.push(datas);
            } else if (docType === 'extraUpload') {
              this.extra.push(datas);
            }
            this.description = '';
            this.spinner.hide();
            // this.gotoPay();
            // this.description1 = '';
            // this.description2 = '';
            // this.description3 = '';
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }
  }

  loadUploadedFile() {

    const data = {
      comId: this.companyId,
      type: 'submit',
      requestId: this.requestId,
    };
    this.rrAddressChangeService.rrFiles(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['file']) {
              this.application = [];
              this.extra = [];
              for (let i in req['data']['file']) {
                const data1 = {
                  id: req['data']['file'][i]['id'],
                  name: req['data']['file'][i]['docname'],
                  key: req['data']['file'][i]['docKey'],
                  token: req['data']['file'][i]['file_token'],
                  pdfname: req['data']['file'][i]['name'],
                  description: req['data']['file'][i]['description'],
                  file_description: req['data']['file'][i]['file_description'],
                };
                if (req['data']['file'][i]['docKey'] === 'FORM_14') {
                  this.application.push(data1);

                } else if (req['data']['file'][i]['docKey'] === 'EXTRA_DOCUMENT') {
                  this.extra.push(data1);
                }
              }
              // this.gotoPay();
            }


          }
        }
      );
  }

  // for view the uploaded pdf...
  ngOnDownload(token: string): void {
    this.spinner.show();
    this.general.getDocumenttoServer(token)
      .subscribe(
        response => {
          this.helper.download(response);
          this.spinner.hide();
        },
        error => {
          this.spinner.hide();
        }
      );
  }

  // for delete the uploaded pdf from the database...
  fileDelete(docId, docType, index) {
    if (confirm('Are you sure you want to delete this document?')) {
      const data = {
        documentId: docId,
      };
      this.spinner.show();
      this.rrAddressChangeService.rrDeleteUploadedPdf(data)
        .subscribe(
          response => {


            if (response['status']) {

              this.loadUploadedFile();
              this.spinner.hide();
            }
          },
          error => {
            this.spinner.hide();
            console.log(error);
          }
        );
    }

  }

  getCipherToken() {
    if (!this.companyId) { return this.router.navigate(['dashboard/home']); }

    const item: Array<Item> = [{
      fee_type: 'PAYMENT_COMPANY_RECORDS_REGISTER_ADDRESS_CHANGE',
      description: `For Company Records Register Address Change - ${this.companyRegno}`,
      quantity: 1,
    }];

    const buy: IBuy = {
      module_type: 'MODULE_COMPANY_RECORDS_REGISTER_ADDRESS_CHANGE',
      module_id: this.requestId,
      description: 'Company Records Register Address Change',
      item: item,
      extraPay: null,
      penalty: (this.court_status === 'yes') ? '0' :  this.penaltyvalue.toString()
    };

    this.crToken.getCrToken(buy).subscribe(
      req => {
        this.cipher_message = req.token;
        this.blockPayment = true;

      },
      error => { console.log(error); }
    );
  }

  showToggle(userType, index = 0) {

    if (userType === 'president') {

      // tslint:disable-next-line:prefer-const
      this.addresses[index]['showEditPaneForPresident'] = !this.addresses[index]['showEditPaneForPresident'];
      return true;


    }
    if (userType === 'share') {

      // tslint:disable-next-line:prefer-const
      this.Saddresses[index]['showEditPaneForPresident'] = !this.Saddresses[index]['showEditPaneForPresident'];
      return true;


    }
    if (userType === 'member') {

      // tslint:disable-next-line:prefer-const
      this.Maddresses[index]['showEditPaneForPresident'] = !this.Maddresses[index]['showEditPaneForPresident'];
      return true;


    }
    if (userType === 'opresident') {

      // tslint:disable-next-line:prefer-const
      this.oldaddresses[index]['showEditPaneForPresident'] = !this.oldaddresses[index]['showEditPaneForPresident'];
      return true;


    }
    if (userType === 'oshare') {

      // tslint:disable-next-line:prefer-const
      this.Soldaddresses[index]['showEditPaneForPresident'] = !this.Soldaddresses[index]['showEditPaneForPresident'];
      return true;


    }
    if (userType === 'omember') {

      // tslint:disable-next-line:prefer-const
      this.Moldaddresses[index]['showEditPaneForPresident'] = !this.Moldaddresses[index]['showEditPaneForPresident'];
      return true;


    }

  }

  // reset functions
  resetPresidentRecord() {
    // tslint:disable-next-line:prefer-const
    let conf = confirm('Are you sure you want to reset ?');

    if (!conf) {
      return true;
    }
    // tslint:disable-next-line:max-line-length
    this.address = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, discription: null };
    this.validatePresident();
    this.addressValidationMessage = '';
  }

  resetShareRegister() {
    // tslint:disable-next-line:prefer-const
    let conf = confirm('Are you sure you want to reset ?');

    if (!conf) {
      return true;
    }
    // tslint:disable-next-line:max-line-length
    this.saddress = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, discription: null };
    this.validateShare();
    this.addressValidationMessage = '';
  }

  resetMemberRegister() {
    // tslint:disable-next-line:prefer-const
    let conf = confirm('Are you sure you want to reset ?');

    if (!conf) {
      return true;
    }
    // tslint:disable-next-line:max-line-length
    this.maddress = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, discription: null };
    this.validateMember();
    this.addressValidationMessage = '';
  }

   // for Records addresses //

  validatePresident() {

    if (!
      (
        this.address.date &&
        this.address.province &&
        this.address.district &&
        this.address.city &&
        this.address.gnDivision &&
        this.address.localAddress1 &&
        this.address.localAddress2 &&
        this.address.postcode && this.postcode(this.address.postcode) &&
        this.address.discription



      )


    ) {


      this.addressValidationMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validAddress = false;

      return false;
    } else {

      this.addressValidationMessage = '';
      this.validAddress = true;
      return true;

    }


  }


  addPresidentDataToArray() {
    for ( var i = 0; i < this.address['discription'].length; i++) {
      const data = {
        id: 0,
        showEditPaneForPresident: 0,
        province: this.address.province.description_en,
        district: this.address.district.description_en,
        city: this.address.city.description_en,
        gnDivision: this.address.gnDivision.description_en,
        localAddress1: this.address['localAddress1'],
        localAddress2: this.address['localAddress2'],
        postcode: this.address['postcode'],
        date: this.address['date'],
        discription: this.address['discription'][i],
      };
      this.addresses.push(data);
   }
    this.address = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, discription: null };
    this.dataSubmit();
  }


  validatePresidentEdit(i = 0) {

    if (!
      (
        this.addresses[i].province &&
        this.addresses[i].district &&
        this.addresses[i].city &&
        this.addresses[i].gnDivision &&
        this.addresses[i].localAddress1 &&
        this.addresses[i].localAddress2 &&
        this.addresses[i].postcode &&  this.postcode(this.addresses[i].postcode) &&
        this.addresses[i].date &&
        this.addresses[i].discription



      )


    ) {



      this.addresses[i]['validEdit'] = false;
      return false;
    } else {


      this.addresses[i]['validEdit'] = true;
      return true;

    }


  }

  validatePresidentOldEdit(i = 0) {

    if (!
      (
        this.oldaddresses[i].province &&
        this.oldaddresses[i].district &&
        this.oldaddresses[i].city &&
        this.oldaddresses[i].gnDivision &&
        this.oldaddresses[i].localAddress1 &&
        this.oldaddresses[i].localAddress2 &&
        this.oldaddresses[i].postcode &&  this.postcode(this.oldaddresses[i].postcode) &&
        this.oldaddresses[i].type && (this.oldaddresses[i].type === 'remove' ? this.oldaddresses[i].remdate : ((this.oldaddresses[i].type === 'move' || this.oldaddresses[i].type === 'pmove') ? this.oldaddresses[i].date : false)) &&
        this.oldaddresses[i].discription



      )


    ) {



     // this.enableStep2SubmissionEdit = false;
      this.oldaddresses[i]['validEdit'] = false;
      return false;
    } else {


     // this.enableStep2SubmissionEdit = true;
      this.oldaddresses[i]['validEdit'] = true;
      return true;

    }


  }

  editPresidentDataArray(i = 0) {
    this.province = this.addresses[i].province;
    this.district = this.addresses[i].district;
    this.city = this.addresses[i].city;
    this.gnDivision = this.addresses[i].gnDivision;
    this.id = this.addresses[i].id;
    const data = {
      id: this.id === 0 ? 0 : this.id,
      showEditPaneForPresident: 0,
      province: this.addresses[i].province.description_en === undefined ? this.province : this.addresses[i].province.description_en,
      district: this.addresses[i].district.description_en === undefined ? this.district : this.addresses[i].district.description_en,
      city: this.addresses[i].city.description_en === undefined ? this.city : this.addresses[i].city.description_en,
      gnDivision: this.addresses[i].gnDivision.description_en === undefined ? this.gnDivision : this.addresses[i].gnDivision.description_en,
      localAddress1: this.addresses[i]['localAddress1'],
      localAddress2: this.addresses[i]['localAddress2'],
      postcode: this.addresses[i]['postcode'],
      date: this.addresses[i]['date'],
      discription: this.addresses[i]['discription'],

    };
    this.addresses.splice(i, 1, data);
    this.enableStep2SubmissionEdit = true;
    this.dataSubmit();


  }

  addDeletingAddresses (id, index){
    if (confirm('If you don’t add a new address, please note that the registered office address will be assigned as the new address')) {
    this.deladdresses.push(id);
    this.oldaddresses[index]['showEditPaneForPresident'] = false;
    console.log(this.deladdresses);
    }
  }

  removeDeletingAddresses (id, index){
    for ( var i = 0; i < this.deladdresses.length; i++) {
      if ( this.deladdresses[i] === id) {
        this.deladdresses.splice(i, 1);
      }
   }
   this.oldaddresses[index]['showEditPaneForPresident'] = true;
   console.log(this.deladdresses);
  }

  // for Records addresses //

  // for Share register addresses //


  validateShare() {
    console.log(this.saddress.discription);

    if (!
      (
        this.saddress.date &&
        this.saddress.province &&
        this.saddress.district &&
        this.saddress.city &&
        this.saddress.gnDivision &&
        this.saddress.localAddress1 &&
        this.saddress.localAddress2 &&
        this.saddress.postcode && this.postcode(this.saddress.postcode) &&
        this.saddress.discription



      )


    ) {


      this.addressValidationMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validSAddress = false;

      return false;
    } else {

      this.addressValidationMessage = '';
      this.validSAddress = true;
      return true;

    }


  }


  addShareDataToArray() {
    for ( var i = 0; i < this.saddress['discription'].length; i++) {
      const data = {
        id: 0,
        showEditPaneForPresident: 0,
        province: this.saddress.province.description_en,
        district: this.saddress.district.description_en,
        city: this.saddress.city.description_en,
        gnDivision: this.saddress.gnDivision.description_en,
        localAddress1: this.saddress['localAddress1'],
        localAddress2: this.saddress['localAddress2'],
        postcode: this.saddress['postcode'],
        date: this.saddress['date'],
        discription: this.saddress['discription'][i],
      };
      this.Saddresses.push(data);
   }
    this.saddress = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, discription: null };
    this.dataSubmit();
  }

  validateShareOldEdit(i = 0) {

    if (!
      (
        this.Soldaddresses[i].province &&
        this.Soldaddresses[i].district &&
        this.Soldaddresses[i].city &&
        this.Soldaddresses[i].gnDivision &&
        this.Soldaddresses[i].localAddress1 &&
        this.Soldaddresses[i].localAddress2 &&
        this.Soldaddresses[i].postcode &&  this.postcode(this.Soldaddresses[i].postcode) &&
        this.Soldaddresses[i].type && (this.Soldaddresses[i].type === 'remove' ? this.Soldaddresses[i].remdate : ((this.Soldaddresses[i].type === 'move' || this.Soldaddresses[i].type === 'pmove') ? this.Soldaddresses[i].date : false)) &&
        this.Soldaddresses[i].discription



      )


    ) {



    //  this.enableStep2SubmissionEdit = false;
      this.Soldaddresses[i]['validEdit'] = false;
      return false;
    } else {


    //  this.enableStep2SubmissionEdit = true;
      this.Soldaddresses[i]['validEdit'] = true;
      return true;

    }


  }


  validateShareEdit(i = 0) {

    if (!
      (
        this.Saddresses[i].province &&
        this.Saddresses[i].district &&
        this.Saddresses[i].city &&
        this.Saddresses[i].gnDivision &&
        this.Saddresses[i].localAddress1 &&
        this.Saddresses[i].localAddress2 &&
        this.Saddresses[i].postcode &&  this.postcode(this.Saddresses[i].postcode) &&
        this.Saddresses[i].date &&
        this.Saddresses[i].discription



      )


    ) {



      this.Saddresses[i]['validEdit'] = false;
      return false;
    } else {


      this.Saddresses[i]['validEdit'] = true;
      return true;

    }


  }

  editShareDataArray(i = 0) {
    this.province = this.Saddresses[i].province;
    this.district = this.Saddresses[i].district;
    this.city = this.Saddresses[i].city;
    this.gnDivision = this.Saddresses[i].gnDivision;
    this.id = this.Saddresses[i].id;
    const data = {
      id: this.id === 0 ? 0 : this.id,
      showEditPaneForPresident: 0,
      province: this.Saddresses[i].province.description_en === undefined ? this.province : this.Saddresses[i].province.description_en,
      district: this.Saddresses[i].district.description_en === undefined ? this.district : this.Saddresses[i].district.description_en,
      city: this.Saddresses[i].city.description_en === undefined ? this.city : this.Saddresses[i].city.description_en,
      gnDivision: this.Saddresses[i].gnDivision.description_en === undefined ? this.gnDivision : this.Saddresses[i].gnDivision.description_en,
      localAddress1: this.Saddresses[i]['localAddress1'],
      localAddress2: this.Saddresses[i]['localAddress2'],
      postcode: this.Saddresses[i]['postcode'],
      date: this.Saddresses[i]['date'],
      discription: this.Saddresses[i]['discription'],

    };
    this.Saddresses.splice(i, 1, data);
    this.enableStep2SubmissionEdit = true;
    this.dataSubmit();


  }

  addShareDeletingAddresses (id, index){
    if (confirm('If you don’t add a new address, please note that the registered office address will be assigned as the new address')) {
    this.Sdeladdresses.push(id);
    this.Soldaddresses[index]['showEditPaneForPresident'] = false;
    console.log(this.Sdeladdresses);
    }
  }

  removeShareDeletingAddresses (id, index){
    for ( var i = 0; i < this.Sdeladdresses.length; i++) {
      if ( this.Sdeladdresses[i] === id) {
        this.Sdeladdresses.splice(i, 1);
      }
   }
   this.Soldaddresses[index]['showEditPaneForPresident'] = true;
   console.log(this.Sdeladdresses);
  }


  // for Share register addresses //


  // for Member register addresses //


  validateMember() {
    console.log(this.maddress.discription);

    if (!
      (
        this.maddress.date &&
        this.maddress.province &&
        this.maddress.district &&
        this.maddress.city &&
        this.maddress.gnDivision &&
        this.maddress.localAddress1 &&
        this.maddress.localAddress2 &&
        this.maddress.postcode && this.postcode(this.maddress.postcode) &&
        this.maddress.discription



      )


    ) {


      this.addressValidationMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validMAddress = false;

      return false;
    } else {

      this.addressValidationMessage = '';
      this.validMAddress = true;
      return true;

    }


  }


  addMemberDataToArray() {
    for ( var i = 0; i < this.maddress['discription'].length; i++) {
      const data = {
      id: 0,
      showEditPaneForPresident: 0,
      province: this.maddress.province.description_en,
      district: this.maddress.district.description_en,
      city: this.maddress.city.description_en,
      gnDivision: this.maddress.gnDivision.description_en,
      localAddress1: this.maddress['localAddress1'],
      localAddress2: this.maddress['localAddress2'],
      postcode: this.maddress['postcode'],
      date: this.maddress['date'],
      discription: this.maddress['discription'][i],
      };
      this.Maddresses.push(data);
   }
    this.maddress = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, discription: null };
    this.dataSubmit();
  }

  validateMemberOldEdit(i = 0) {

    if (!
      (
        this.Moldaddresses[i].province &&
        this.Moldaddresses[i].district &&
        this.Moldaddresses[i].city &&
        this.Moldaddresses[i].gnDivision &&
        this.Moldaddresses[i].localAddress1 &&
        this.Moldaddresses[i].localAddress2 &&
        this.Moldaddresses[i].postcode &&  this.postcode(this.Moldaddresses[i].postcode) &&
        this.Moldaddresses[i].type && (this.Moldaddresses[i].type === 'remove' ? this.Moldaddresses[i].remdate : ((this.Moldaddresses[i].type === 'move' || this.Moldaddresses[i].type === 'pmove') ? this.Moldaddresses[i].date : false)) &&
        this.Moldaddresses[i].discription



      )


    ) {



     // this.enableStep2SubmissionEdit = false;
      this.Moldaddresses[i]['validEdit'] = false;
      return false;
    } else {


     // this.enableStep2SubmissionEdit = true;
      this.Moldaddresses[i]['validEdit'] = true;
      return true;

    }


  }


  validateMemberEdit(i = 0) {

    if (!
      (
        this.Maddresses[i].province &&
        this.Maddresses[i].district &&
        this.Maddresses[i].city &&
        this.Maddresses[i].gnDivision &&
        this.Maddresses[i].localAddress1 &&
        this.Maddresses[i].localAddress2 &&
        this.Maddresses[i].postcode &&  this.postcode(this.Maddresses[i].postcode) &&
        this.Maddresses[i].date &&
        this.Maddresses[i].discription



      )


    ) {



      this.Maddresses[i]['validEdit'] = false;
      return false;
    } else {


      this.Maddresses[i]['validEdit'] = true;
      return true;

    }


  }

  editMemberDataArray(i = 0) {
    this.province = this.Maddresses[i].province;
    this.district = this.Maddresses[i].district;
    this.city = this.Maddresses[i].city;
    this.gnDivision = this.Maddresses[i].gnDivision;
    this.id = this.Maddresses[i].id;
    const data = {
      id: this.id === 0 ? 0 : this.id,
      showEditPaneForPresident: 0,
      province: this.Maddresses[i].province.description_en === undefined ? this.province : this.Maddresses[i].province.description_en,
      district: this.Maddresses[i].district.description_en === undefined ? this.district : this.Maddresses[i].district.description_en,
      city: this.Maddresses[i].city.description_en === undefined ? this.city : this.Maddresses[i].city.description_en,
      gnDivision: this.Maddresses[i].gnDivision.description_en === undefined ? this.gnDivision : this.Maddresses[i].gnDivision.description_en,
      localAddress1: this.Maddresses[i]['localAddress1'],
      localAddress2: this.Maddresses[i]['localAddress2'],
      postcode: this.Maddresses[i]['postcode'],
      date: this.Maddresses[i]['date'],
      discription: this.Maddresses[i]['discription'],

    };
    this.Maddresses.splice(i, 1, data);
    this.enableStep2SubmissionEdit = true;
    this.dataSubmit();


  }

  addMemberDeletingAddresses (id, index){
    if (confirm('If you don’t add a new address, please note that the registered office address will be assigned as the new address')) {
    this.Mdeladdresses.push(id);
    this.Moldaddresses[index]['showEditPaneForPresident'] = false;
    console.log(this.Mdeladdresses);
    }
  }

  removeMemberDeletingAddresses (id, index){
    for ( var i = 0; i < this.Mdeladdresses.length; i++) {
      if ( this.Mdeladdresses[i] === id) {
        this.Mdeladdresses.splice(i, 1);
      }
   }
   this.Moldaddresses[index]['showEditPaneForPresident'] = true;
   console.log(this.Mdeladdresses);
  }


  // for Member register addresses //

  getEmail() {

    this.email = localStorage.getItem('currentUser');
    this.email = this.email.replace(/^"(.*)"$/, '$1');
    return this.email;
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

  private postcode(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let code = /^[0-9]+$/;
    return inputtxt.match(code);
  }

  revertRecord(userType, id) {
    let conf = confirm('Are you sure you want to revert ?');
    if (!conf) {
      return true;
    }
    const data = {
      companyId: this.companyId,
      reqid: this.requestId,
      email: this.getEmail(),
      // records: this.oldaddresses,
      // shares: this.Soldaddresses,
      // members: this.Moldaddresses,
      // signby: this.signbyid,
      type: userType,
      id: id,
    };
    this.spinner.show();
    this.rrAddressChangeService.rrAddressRevert(data)
      .subscribe(
        req => {
          if (req['status']) {


          }
          this.loadCompanyAddressProcessing();
          // this.memberload();
        },
        error => {
          console.log(error);
          this.spinner.hide();
        }
      );

  }

  deleteRecordnew(userType, id) {
    let conf = confirm('Are you sure you want to delete ?');
    console.log(conf);
    console.log(this.addresses);
    if (!conf) {
      console.log('intheconf');
      console.log(this.addresses);
      return true;
    }
    console.log('outtheconf');
    console.log(this.addresses);
    const data = {
      companyId: this.companyId,
      reqid: this.requestId,
      email: this.getEmail(),
      // records: this.oldaddresses,
      // shares: this.Soldaddresses,
      // members: this.Moldaddresses,
      // signby: this.signbyid,
      type: userType,
      id: id,
    };
    this.spinner.show();
    this.rrAddressChangeService.rrAddressDelete(data)
      .subscribe(
        req => {
          if (req['status']) {


          }
          this.loadCompanyAddressProcessing();
          // this.memberload();
        },
        error => {
          console.log(error);
          this.spinner.hide();
        }
      );

  }

}
