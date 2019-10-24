import { Component, OnInit, AfterViewInit } from '@angular/core';
import { DataService } from '../../../../../../storage/data.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { IAddressData, IOldAddressData, IAcOldAddressData, IAcAddressData } from '../../../../../../http/models/address.model';
import { AccountingAddressChangeService } from '../../../../../../http/services/accounting-address-change.service';
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
  selector: 'app-accounting-address-change',
  templateUrl: './accounting-address-change.component.html',
  styleUrls: ['./accounting-address-change.component.scss']
})
export class AccountingAddressChangeComponent implements OnInit, AfterViewInit {


  progress = {

    stepArr: [
      { label: 'Accounting Address Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },

    ],

    progressPercentage: '12.5%'

  };

  document_confirm = false;
  companyId: string;
  changeId: string;
  oldaddressId: string;
  companyName: string;
  companyRegno: string;
  members: any;
  signbyid: any;
  convert: any;
  requestId: string;
  effectiveDate: string;
  blockBackToForm = false;
  blockPayment = false;
  enableGoToPay = false;
  storage1: any;
  storage2: any;
  itemcount: any;
  penaltyvalue: any;
  penalty_charge: any;
  application = [];
  addchanges = [];
  remchanges = [];
  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;
  addressValidationMessage: any;
  date = new Date();
  email = '';
  stepOn = 0;
  cipher_message: string;
  validAddress = false;
  addresses = [];
  oldaddresses = [];
  deladdresses = [];
  address: IAcAddressData = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, country: null, type: 1, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null };
  oldaddress: IAcOldAddressData = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, country: null, type: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null };
  enableStep1Submission: boolean;
  enableStep2SubmissionEdit = false;
  addlist = [];
  remlist = [];
  extra = [];
  postfix: string;
  mindate: string;
  incoDate: string;
  minDate: string;
  countries: any;

  province: string;
  district: string;
  city: string;
  gnDivision: string;
  id: any;
  adType: any;

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
  description: string;

  constructor(private router: Router,
    public calculation: CalculationService,
    private crToken: PaymentService,
    private helper: HelperService,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    public data: DataService,
    private general: GeneralService,
    private accountingAddressChangeService: AccountingAddressChangeService) {

    if (JSON.parse(localStorage.getItem('ACstorage'))) {
      this.storage2 = JSON.parse(localStorage.getItem('ACstorage'));

      if (this.storage2['comId'] === JSON.parse(localStorage.getItem('ACcompanyId')) && JSON.parse(localStorage.getItem('ACstatus')) === 'processing') {
        this.storage1 = JSON.parse(localStorage.getItem('ACstorage'));
        this.companyId = this.storage1['comId'];
        this.requestId = this.storage1['changeReqId'];
        console.log(this.companyId);
        console.log(this.requestId);
        this.loadCompanyAddressProcessing();
        // this.loadUploadedFile();
        //  this.changeProgressStatuses(2);
      }
      else {
        this.companyId = JSON.parse(localStorage.getItem('ACcompanyId'));
        console.log(this.companyId);
        this.loadCompanyAddress();
      }

    }
    else {
      this.companyId = JSON.parse(localStorage.getItem('ACcompanyId'));
      console.log(this.companyId);
      this.loadCompanyAddress();
    }
  }

  ngOnInit() {
    this.convertAndAdd();
    this.formattedTodayValue = this.getFormatedToday();
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

  convertAndAdd() {
    // tslint:disable-next-line:prefer-const
    let dt = new Date();
    dt.setDate(dt.getDate() - 1);
    var Y = dt.getFullYear().toString();
    var m = (dt.getMonth() + 1).toString();
    var d = dt.getDate().toString();
    var D = d.length === 1 ? '0' + d : d;
    var M = m.length === 1 ? '0' + m : m;
    this.mindate = Y + '-' + M + '-' + D;
  }


  loadCompanyAddress() {
    const data = {
      id: this.companyId,
      type: 'submit'
    };
    this.accountingAddressChangeService.loadCompanyAddress(data)
      .subscribe(
        req => {
          if (req['data']) {
            if (req['data']['address']) {
              for (let i in req['data']['address']) {
                const data1 = {
                  id: req['data']['address'][i]['oid'],
                  showEditPaneForPresident: true,
                  province: req['data']['address'][i]['province'],
                  district: req['data']['address'][i]['district'],
                  city: req['data']['address'][i]['city'],
                  gnDivision: req['data']['address'][i]['gnDivision'],
                  localAddress1: req['data']['address'][i]['address1'],
                  localAddress2: req['data']['address'][i]['address2'],
                  postcode: req['data']['address'][i]['postcode'],
                  date: req['data']['address'][i]['date'],
                  country: req['data']['address'][i]['country'],
                };
                this.oldaddresses.push(data1);
              }
            }
            // this.oldaddress.localAddress1 = req['data']['address']['address1'];
            // this.oldaddress.localAddress2 = req['data']['address']['address2'];
            // this.oldaddress.province = req['data']['address']['province'];
            // this.oldaddress.district = req['data']['address']['district'];
            // this.oldaddress.city = req['data']['address']['city'];
            // this.oldaddress.postcode = req['data']['address']['postcode'];
            // this.oldaddress.gnDivision = req['data']['address']['gn_division'];
            // this.oldaddressId = req['data']['address']['id'];
            this.companyName = req['data']['company'][0]['name'];
            this.postfix = req['data']['company'][0]['postfix'];
            this.companyRegno = req['data']['company'][0]['registration_no'];
            this.incoDate = req['data']['company'][0]['incorporation_at'];
            this.members = req['data']['members'];
            this.minDate = req['data']['mindate'];
            this.countries = req['data']['countries'];

          }
        },
        error => {
          console.log(error);
        },
        () => {
          if (this.minDate) {
            this.incoDate = this.minDate;
          }
        }
      );
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

  loadCompanyAddressProcessing() {
    const data = {
      id: this.companyId,
      type: 'processing',
      requestID: this.storage1['changeReqId']
    };
    this.accountingAddressChangeService.loadCompanyAddress(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.addresses = [];
            this.oldaddresses = [];
            if (req['data']['address']) {
              for (let i in req['data']['address']) {
                const data1 = {
                  id: req['data']['address'][i]['oid'],
                  showEditPaneForPresident: true,
                  province: req['data']['address'][i]['province'],
                  district: req['data']['address'][i]['district'],
                  city: req['data']['address'][i]['city'],
                  gnDivision: req['data']['address'][i]['gnDivision'],
                  localAddress1: req['data']['address'][i]['address1'],
                  localAddress2: req['data']['address'][i]['address2'],
                  postcode: req['data']['address'][i]['postcode'],
                  date: req['data']['address'][i]['date'],
                  country: req['data']['address'][i]['country'],
                };
                this.oldaddresses.push(data1);
              }
            }
            if (req['data']['addressactive']) {
              for (let i in req['data']['addressactive']) {
                if (req['data']['addressactive'][i]['type'] === 'DELETE') {
                  const data1 = {
                    id: req['data']['addressactive'][i]['oid'],
                    showEditPaneForPresident: false,
                    province: req['data']['addressactive'][i]['province'],
                    district: req['data']['addressactive'][i]['district'],
                    city: req['data']['addressactive'][i]['city'],
                    gnDivision: req['data']['addressactive'][i]['gnDivision'],
                    localAddress1: req['data']['addressactive'][i]['address1'],
                    localAddress2: req['data']['addressactive'][i]['address2'],
                    postcode: req['data']['addressactive'][i]['postcode'],
                    date: req['data']['addressactive'][i]['date'],
                    type: req['data']['addressactive'][i]['type'],
                    country: req['data']['addressactive'][i]['country'],
                  };
                  // this.oldaddresses.push(data1);
                  this.deladdresses.push(data1['id']);
                }
                // else {
                //   const data1 = {
                //     id: req['data']['addressactive'][i]['oid'],
                //     showEditPaneForPresident: true,
                //     province: req['data']['addressactive'][i]['province'],
                //     district: req['data']['addressactive'][i]['district'],
                //     city: req['data']['addressactive'][i]['city'],
                //     gnDivision: req['data']['addressactive'][i]['gnDivision'],
                //     localAddress1: req['data']['addressactive'][i]['address1'],
                //     localAddress2: req['data']['addressactive'][i]['address2'],
                //     postcode: req['data']['addressactive'][i]['postcode'],
                //     date: req['data']['addressactive'][i]['date'],
                //     type: req['data']['addressactive'][i]['type'],
                //     country: req['data']['addressactive'][i]['country'],
                //   };
                //   this.oldaddresses.push(data1);
                // }
              }
              this.checkDoubleRecord();
            }
            if (req['data']['addresspending']) {
              for (let i in req['data']['addresspending']) {
                var type;
                if (req['data']['addresspending'][i]['country'] === 'Sri Lanka') {
                  type = 1;
                }
                else{
                  type = 2;
                }
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
                  country: req['data']['addresspending'][i]['country'],
                  type: type,
                };
                this.addresses.push(data1);
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
            this.postfix = req['data']['company'][0]['postfix'];
            this.companyRegno = req['data']['company'][0]['registration_no'];
            this.incoDate = req['data']['company'][0]['incorporation_at'];
            this.members = req['data']['members'];
            this.minDate = req['data']['mindate'];
            this.countries = req['data']['countries'];
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
          }
          this.validateCourtSection();
          // this.addressValidationStep1();
        },
        error => {
          console.log(error);
        },
        () => {
          if (this.minDate) {
            this.incoDate = this.minDate;
          }
        }
      );
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

  showToggle(userType, index = 0) {

    if (userType === 'president') {

      // tslint:disable-next-line:prefer-const
      this.addresses[index]['showEditPaneForPresident'] = !this.addresses[index]['showEditPaneForPresident'];
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
    this.address = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, country: null, type: 1, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null };
    this.validatePresident();
    this.addressValidationMessage = '';
  }

  selectAddressType(typ = 0) {
    if (typ === 1) {
      this.address = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, country: null, type: 1, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null };
    }
    else if (typ === 2) {
      this.address = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, country: null, type: 2, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null };
    }

    // this.address.type = typ;
    this.validatePresident();



  }

  validatePresident() {
    if (this.address.type === 1) {

      if (!
        (
          this.address.date &&
          this.address.province &&
          this.address.district &&
          this.address.city &&
          this.address.gnDivision &&
          this.address.localAddress1 &&
          this.address.localAddress2 &&
          this.address.postcode && this.postcode(this.address.postcode)



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
    else if (this.address.type === 2) {

      if (!
        (
          this.address.date &&
          this.address.province &&
          this.address.city &&
          this.address.localAddress1 &&
          this.address.country &&
          this.address.postcode && this.postcode(this.address.postcode)



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


  }


  addPresidentDataToArray() {
    if (this.address.type === 1) {
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
        country: this.address['country'],
        type: this.address.type,
      };
      this.addresses.push(data);
      this.address = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, country: null, type: 1, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null };

    }
    else {
      const data = {
        id: 0,
        showEditPaneForPresident: 0,
        province: this.address['province'],
        district: this.address['district'],
        gnDivision: this.address['gnDivision'],
        country: this.address['country'],
        city: this.address['city'],
        localAddress1: this.address['localAddress1'],
        localAddress2: this.address['localAddress2'],
        postcode: this.address['postcode'],
        date: this.address['date'],
        type: this.address.type,
      };
      this.addresses.push(data);
      this.address = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, country: null, type: 2, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null };
    }
  }


  validatePresidentEdit(i = 0) {
    if (this.addresses[i].type === 1) {
      if (!
        (
          this.addresses[i].province &&
          this.addresses[i].district &&
          this.addresses[i].city &&
          this.addresses[i].gnDivision &&
          this.addresses[i].localAddress1 &&
          this.addresses[i].localAddress2 &&
          this.addresses[i].postcode && this.postcode(this.addresses[i].postcode) &&
          this.addresses[i].date



        )


      ) {



        this.enableStep2SubmissionEdit = false;
        return false;
      } else {


        this.enableStep2SubmissionEdit = true;
        return true;

      }
    }
    else if (this.addresses[i].type === 2) {

      if (!
        (
          this.addresses[i].province &&
          this.addresses[i].city &&
          this.addresses[i].country &&
          this.addresses[i].localAddress1 &&
          this.addresses[i].postcode && this.postcode(this.addresses[i].postcode) &&
          this.addresses[i].date



        )


      ) {



        this.enableStep2SubmissionEdit = false;
        return false;
      } else {


        this.enableStep2SubmissionEdit = true;
        return true;

      }

    }


  }

  editPresidentDataArray(i = 0) {
    if (this.addresses[i].type === 1) {
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
        country: this.addresses[i]['country'],
        postcode: this.addresses[i]['postcode'],
        date: this.addresses[i]['date'],
        type: this.addresses[i].type,

      };
      this.addresses.splice(i, 1, data);
      this.enableStep2SubmissionEdit = true;

    }
    else if (this.addresses[i].type === 2) {
      this.id = this.addresses[i].id;
      const data = {
        id: this.id === 0 ? 0 : this.id,
        showEditPaneForPresident: 0,
        province: this.addresses[i]['province'],
        district: this.addresses[i]['district'],
        gnDivision: this.addresses[i]['gnDivision'],
        city: this.addresses[i]['city'],
        country: this.addresses[i]['country'],
        localAddress1: this.addresses[i]['localAddress1'],
        localAddress2: this.addresses[i]['localAddress2'],
        postcode: this.addresses[i]['postcode'],
        date: this.addresses[i]['date'],
        type: this.addresses[i].type,

      };
      this.addresses.splice(i, 1, data);
      this.enableStep2SubmissionEdit = true;

    }


  }

  addDeletingAddresses(id, index) {
    this.deladdresses.push(id);
    this.oldaddresses[index]['showEditPaneForPresident'] = false;
    console.log(this.deladdresses);
  }

  removeDeletingAddresses(id, index) {
    for (var i = 0; i < this.deladdresses.length; i++) {
      if (this.deladdresses[i] === id) {
        this.deladdresses.splice(i, 1);
      }
    }
    this.oldaddresses[index]['showEditPaneForPresident'] = true;
    console.log(this.deladdresses);
  }

  getEmail() {

    this.email = localStorage.getItem('currentUser');
    this.email = this.email.replace(/^"(.*)"$/, '$1');
    return this.email;
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

    this.accountingAddressChangeService.accountingAddressCourtDataSubmit(data)
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

  dataSubmit() {


    const data = {
      reqid: this.requestId,
      id: this.companyId,
      delArr: this.deladdresses,
      addArr: this.addresses,
      email: this.getEmail(),
      signby: this.signbyid

    };

    this.accountingAddressChangeService.accountingAddressDataSubmit(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['changes']) {
              this.addchanges = [];
              this.remchanges = [];
              for (let i in req['data']['changes']) {
                const data1 = {
                  id: req['data']['changes'][i]['item_id'],
                  type: req['data']['changes'][i]['type'],
                  changeid: req['data']['changes'][i]['id'],
                  province: req['data']['changes'][i]['province'],
                  district: req['data']['changes'][i]['district'],
                  city: req['data']['changes'][i]['city'],
                  gnDivision: req['data']['changes'][i]['gnDivision'],
                  localAddress1: req['data']['changes'][i]['address1'],
                  localAddress2: req['data']['changes'][i]['address2'],
                  postcode: req['data']['changes'][i]['postcode'],
                  date: req['data']['changes'][i]['date'],
                  country: req['data']['changes'][i]['country'],
                };
                if (req['data']['changes'][i]['type'] === 'ADD') {
                  this.addchanges.push(data1);

                } else if (req['data']['changes'][i]['type'] === 'DELETE') {
                  this.remchanges.push(data1);
                }
              }
              // this.gotoPay();
              this.enableGoToPay = false;
              console.log(this.addchanges, this.remchanges);
            }


          }
          this.requestId = req['reqID'];
          this.penalty_charge = req['penalty_value'];
          if (!this.penalty_charge){
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
          this.validateCourtSection();
          this.loadUploadedFile();
          this.changeProgressStatuses(1);
          // this.memberload();
          this.blockBackToForm = false;
        },
        error => {
          console.log(error);
        }
      );

  }

  // download functions


  form16Download(oid, changeid) {

    const data = {

      comId: this.companyId,
      email: this.getEmail(),
      oid: oid,
      changeid: changeid,
      requestID: this.requestId

    };

    this.accountingAddressChangeService.getPDFService(data).subscribe(
      response => {

        this.helper.download(response);
      },
      error => {
        console.log(error);
      }
    );

  }

  // for uplaod form 16 pdf files...
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
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getAccountingAddressFileUploadUrl();
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
            if (docType === 'addUpload') {
              this.addlist[description] = datas;
            } else if (docType === 'removeUpload') {
              this.remlist[description] = datas;
            } else if (docType === 'extraUpload') {
              this.extra.push(datas);
            }
            this.spinner.hide();
            this.description = '';
            console.log(this.addlist, this.remlist);
            this.gotoPay();
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }



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
      this.accountingAddressChangeService.accountingaddresschangeDeleteUploadedPdf(data)
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

  loadUploadedFile() {

    const data = {
      comId: this.companyId,
      type: 'submit',
      reqid: this.requestId

    };
    this.accountingAddressChangeService.accountingaddresschangeFiles(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['file']) {
              this.addlist = [];
              this.remlist = [];
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
                if (req['data']['file'][i]['docKey'] === 'FORM_16_ADD') {
                  this.addlist[req['data']['file'][i]['description']] = data1;
                } else if (req['data']['file'][i]['docKey'] === 'FORM_16_REMOVE') {
                  this.remlist[req['data']['file'][i]['description']] = data1;
                } else if (req['data']['file'][i]['docKey'] === 'EXTRA_DOCUMENT') {
                  this.extra.push(data1);
                }
              }
              this.gotoPay();
            }


          }
        }
      );
  }

  gotoPay() {

    let x = 0;
    let y = 0;
    for (let item of this.addlist) {
      if (item) {
        x = x + 1;
      }
      else {
        continue;
      }
    }
    for (let item of this.remlist) {
      if (item) {
        y = y + 1;
      }
      else {
        continue;
      }
    }
    if (x === this.addchanges.length && y === this.remchanges.length) {
      this.enableGoToPay = true;
      this.itemcount = x + y;
    }
    else {
      this.enableGoToPay = false;
    }


  }

  getCipherToken() {
    if (!this.companyId) { return this.router.navigate(['dashboard/home']); }
    const item: Array<Item> = [{
      fee_type: 'PAYMENT_COMPANY_ACCOUNTING_ADDRESS_CHANGE',
      description: `For Company Accounting Address Change - ${this.companyRegno}`,
      quantity: this.itemcount,
    }];

    console.log(item);

    const buy: IBuy = {
      module_type: 'MODULE_COMPANY_ACCOUNTING_ADDRESS_CHANGE',
      module_id: this.requestId,
      description: 'Company Accounting Address Change',
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

  private courtamount(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let code = /^[0-9]+$/;
    return inputtxt.match(code);
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

  deleteRecord(userType, i = 0) {

    if (userType === 'p') {


      this.addresses.splice(i, 1);
      this.validAddress = false;
      return true;


    }

  }

}
