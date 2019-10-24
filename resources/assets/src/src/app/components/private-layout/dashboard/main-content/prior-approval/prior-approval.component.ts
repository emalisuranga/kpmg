import { Component, OnInit, AfterViewInit } from '@angular/core';
import { DataService } from '../../../../../storage/data.service';
import { GeneralService } from '../../../../../http/services/general.service';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { NgxSpinnerService } from 'ngx-spinner';
import { APIConnection } from '../../../../../http/services/connections/APIConnection';
import { HelperService } from '../../../../../http/shared/helper.service';
import { PaymentService } from '../../../../../http/services/payment.service';
import { Item, IBuy } from '../../../../../http/models/payment';
import { Router, ActivatedRoute } from '@angular/router';
import { CalculationService } from './../../../../../http/shared/calculation.service';
import * as $ from 'jquery';
import { environment } from '../../../../../../environments/environment';
import { IcompanyType, IcompanyInfo, IloginUser, IloginUserAddress } from '../../../../../http/models/incorporation.model';
import { APIpriorApprovalConnection } from './service/APIpriorApprovalConnection';
import { PirorApprovalService } from './service/piror-approval.service';
import { AlertService } from 'ngx-alerts';
import { from } from 'rxjs';


@Component({
  selector: 'app-prior-approval',
  templateUrl: './prior-approval.component.html',
  styleUrls: ['./prior-approval.component.scss']
})
export class PriorApprovalComponent implements OnInit, AfterViewInit {

  url: APIpriorApprovalConnection = new APIpriorApprovalConnection();

  progress = {

    stepArr: [
      { label: 'Company Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Information', icon: 'fas fa-share', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      // { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },

    ],

    progressPercentage: '18%'
  };

  mainDoc_payment = 0;
  total_wihtout_vat_tax = 0;
  total_with_vat_tax = 0;
  vat = 0;
  vatVal = 0;
  other_tax = 0;
  other_taxVal = 0;
  convinienceFee = 0;
  convinienceFeeVal = 0;
  // company id
  companyId: string;
  changeId: string;
  oldaddressId: string;
  companyName: string;
  companyRegno: string;
  requestId: number;
  loginUserEmail: string;

  // process status
  processStatus: string;
  annualReturnStatus = '';
  resubmitSuccess = false;
  resubmitSuccessMessage = '';
  stepOn = 0;
  externalGlobComment = '';
  requestNumber = '';

  categories = [];
  message: any;
  subject: any;
  payment: any;
  PreBalDate: string;
  ProBalDate: String;
  effectiveYear: String;

  moduleStatus = '';
  formattedTodayValue = '';
  currencies = [];
  other_doc_name = '';
  companyRegNumber = '';
  validShareRecordItem = false;
  allFilesUploaded = false;

  cipher_message: string;
  paymentItems: Array<Item> = [];
  paymentGateway: string = environment.paymentGateway;

  payConfirm = false;

  companyInfo: IcompanyInfo = {
    abbreviation_desc: '', address_id: null, created_at: null, created_by: null, email: '', id: null, name: '', name_si: '', name_ta: '', postfix: '', status: null, type_id: null, updated_at: null, objective1: null, objective2: null, objective3: null, objective4: null, objective5: null, otherObjective: '', incorporation_at: ''
  };

  compayType: IcompanyType = { key: '', value: '', id: null, value_si: '', value_ta: '' };

  uploadList = [];
  uploadOtherList = [];
  date = new Date();
  year = this.date.getFullYear();
  onemoreyear = this.year + 1;
  minDate = this.year + '-01-02';
  maxDate = this.onemoreyear + '-12-31';

  ngAfterViewInit() {

    $(document).on('click', '.record-handler-remove', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      self.parent().parent().remove();
    });



    $('button.add-share-record-row').on('click', function () {
      $('#share-record-modal .close-modal-item').trigger('click');
    });


    $('.stakeholder-type-tab-wrapper.stakeholder-dir .tab').on('click', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      $('.stakeholder-type-tab-wrapper.stakeholder-dir .tab').removeClass('active');
      $(this).addClass('active');

    });

    $('.stakeholder-type-tab-wrapper.stakeholder-sec .tab').on('click', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      $('.stakeholder-type-tab-wrapper.stakeholder-sec .tab').removeClass('active');
      $(this).addClass('active');

    });

    $('.stakeholder-type-tab-wrapper.stakeholder-sh .tab').on('click', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      $('.stakeholder-type-tab-wrapper.stakeholder-sh .tab').removeClass('active');
      $(this).addClass('active');

    });

  }

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    public calculation: CalculationService,
    private crToken: PaymentService,
    private helper: HelperService,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    public data: DataService,
    private general: GeneralService,
    private pirorApprovalService: PirorApprovalService,
    private alertService: AlertService
  ) {

    this.loginUserEmail = localStorage.getItem('currentUser');
    this.companyId = route.snapshot.paramMap.get('companyId');
    this.requestId = Number(route.snapshot.paramMap.get('requestId'));

    // if (!this.requestId){
    //   this.loadCompanyAddress();
    // }else{
    //   this.loadData();
    // }
    this.loadCompanyAddress();

  }

  ngOnInit() { }

  loadCompanyAddress() {
    const data = {
      companyId: this.companyId,
      loginUser: this.loginUserEmail,
      requestId: this.requestId
    };
    this.spinner.show();
    // console.log(data);
    this.pirorApprovalService.loadCompanyAddress(data)
      .subscribe(
        req => {
          this.moduleStatus = req['data']['moduleStatus'];
          // tslint:disable-next-line:radix
          this.requestId = req['data']['request_id'] ? parseInt(req['data']['request_id']) : 0;

          this.compayType = req['data']['companyType'];
          this.processStatus = req['data']['processStatus'];
          this.currencies = req['data']['currencies'];
          this.companyRegNumber = req['data']['certificate_no'];
          this.categories = req['data']['category'];
          this.payment = req['data']['payment'];
          if (req['data']['predate']) {
            if (this.requestId) {
              this.PreBalDate = req['data']['predate']['previous_date'];
              this.ProBalDate = req['data']['predate']['proposed_date'];
              this.effectiveYear = req['data']['predate']['effected_year'];
            }else{
              this.PreBalDate = req['data']['predate']['proposed_date'];
            }
          }

          if (req['data']['record']) {

            this.message = req['data']['record'];
            this.subject = req['data']['subject'];
          }
          this.companyInfo = req['data']['companyInfo'];
          // this.uploadList = req['data']['uploadDocs'];
          this.uploadOtherList = req['data']['uploadOtherDocs'];
          this.allFilesUploaded = this.uploadOtherList['uploadedAll'];
          this.mainDoc_payment = (req['data']['othersCourt_payment']) ? parseFloat(req['data']['othersCourt_payment']) : 0;
          this.total_wihtout_vat_tax = this.mainDoc_payment;
          this.vat = (req['data']['vat']) ? parseFloat(req['data']['vat']) : 0;
          this.other_tax = (req['data']['other_tax']) ? parseFloat(req['data']['other_tax']) : 0;
          this.convinienceFee = (req['data']['convinienceFee']) ? parseFloat(req['data']['convinienceFee']) : 0;
          this.other_taxVal = (this.total_wihtout_vat_tax * this.other_tax) / 100;
          this.vatVal = (this.total_wihtout_vat_tax + this.other_taxVal) * this.vat / 100;
          this.convinienceFeeVal = (this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal) * this.convinienceFee / 100;
          this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;
          this.externalGlobComment = req['data']['external_global_comment'];

          this.validateCharge();
          if (this.payment === '0') {
          } else {
            this.progress.stepArr[3] = { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' };
          }
          if (this.minDate < this.companyInfo['incorporation_at']) {
            this.minDate = this.companyInfo['incorporation_at'];
          }
          console.log(this.minDate + '***' + this.companyInfo['incorporation_at']);
          this.spinner.hide();
        },
        error => {
          alert(error);
          this.spinner.hide();
        }
      );
  }

  changeProgressStatuses(newStatus = 0) {
    if (this.payment === '0') {
      this.stepOn = newStatus;

      this.progress.progressPercentage = (this.stepOn > 1) ? (84) + '%' : (18 + this.stepOn * 29) + '%';

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
    } else {
      this.stepOn = newStatus;
      this.progress.progressPercentage = (this.stepOn >= 4) ? (10 * 2 + this.stepOn * 20) + '%' : (10 + this.stepOn * 20) + '%';
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
  }

  private slugify(text) {
    return text.toString().toLowerCase()
      .replace(/\s+/g, '-')           // Replace spaces with -
      .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
      .replace(/\-\-+/g, '-')         // Replace multiple - with single -
      .replace(/^-+/, '')             // Trim - from start of text
      .replace(/-+$/, '');            // Trim - from end of text
  }

  validateCharge() {
    if (this.subject === 'CHANGE_OF_BALANCE_SHEET_DATE') {
      if (this.subject &&
        this.message &&
        this.ProBalDate &&
        this.effectiveYear &&
        this.PreBalDate
      ) {
        this.validShareRecordItem = true;
      } else {
        this.validShareRecordItem = false;
      }
    } else if (this.subject &&
      this.message) {
      this.validShareRecordItem = true;
    } else {
      this.validShareRecordItem = false;
    }

    // console.log(this.validShareRecordItem);
    // console.log(this.subject);
    // console.log(this.message);
  }

  submitRecord(action = '') {

    const data = {
      companyId: this.companyId,
      requestId: this.requestId,
      loginUser: this.loginUserEmail,
      message: this.message,
      subject: this.subject,
      PreBalDate: this.PreBalDate,
      ProBalDate: this.ProBalDate,
      effectiveYear: this.effectiveYear
    };

    this.pirorApprovalService.shareCallSubmit(data)
      .subscribe(
        req => {
          this.requestId = req['request_id'];
          this.loadCompanyAddress();
          // if (this.payment === '0') {
          //   // this.changeProgressStatuses(1);
          //   // return false;
          // }
          this.changeProgressStatuses(2);
        },
        error => {
          this.changeProgressStatuses(1);
          console.log(error);
        }

      );

  }

  uploadOtherDoc(event, fileNane, fileDBID) {

    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {


      for (let i = 0; i < fileList.length; i++) {

        // tslint:disable-next-line:prefer-const
        let file: File = fileList[i];

        // console.log(fileList[0]);

        // tslint:disable-next-line:prefer-const
        let fileSize = fileList[i].size;

        if (fileSize >= 1024 * 1024 * 10) { // 4mb restriction
          alert('You can upload document only up to 10 MB');
          return false;
        }

        // tslint:disable-next-line:prefer-const
        let formData: FormData = new FormData();
        formData.append('uploadFile', file, file.name);
        formData.append('fileName', this.slugify(fileNane));
        let filename = (this.other_doc_name) ? this.other_doc_name + '.pdf' : file.name;
        formData.append('fileRealName', filename);
        formData.append('fileDescription', this.other_doc_name);
        formData.append('fileTypeId', fileDBID);
        formData.append('company_id', this.companyId);
        let request_id = this.requestId.toString();
        formData.append('request_id', request_id);
        // tslint:disable-next-line:prefer-const
        let headers = new HttpHeaders();
        headers.append('Content-Type', 'multipart/form-data');
        headers.append('Accept', 'application/json');

        // tslint:disable-next-line:prefer-const
        let uploadurl = this.url.uploadOtherDocsURL();
        this.spinner.show();

        this.httpClient.post(uploadurl, formData, { headers: headers })
          .subscribe(
            (data: any) => {
              this.other_doc_name = '';
              this.loadCompanyAddress();
              // this.allFilesUploaded = this.uploadList['uploadedAll'];
              this.spinner.hide();
            },
            error => {
              console.log(error);
              this.spinner.hide();
            }
          );


      }


    }

  }

  removeOtherDoc(token, fileDBID ) {
    const data = {
      file_token: token,
      documentId: fileDBID,
    };
    this.spinner.show();

    // load Company data from the server
    this.pirorApprovalService.removeOtherDoc(data)
      .subscribe(
        req => {
          this.loadCompanyAddress();
        }
      );

  }

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

  submit() {
    const data = {
      companyId: this.companyId,
      requestId: this.requestId,
    };
    this.spinner.show();
    this.pirorApprovalService.submit(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (this.payment = '0') {
              this.alertService.success(req['message']);
              this.router.navigate(['/dashboard/home']);
              this.spinner.hide();
            } else {
              this.alertService.success(req['message']);
              this.changeProgressStatuses(3);
              this.spinner.hide();
            }
          } else {
            this.alertService.warning(req['message']);
            this.loadCompanyAddress();
          }
        }
      );
  }

  resubmit() {
    const data = {
      companyId: this.companyId,
      requestId: this.requestId
    };
    this.spinner.show();

    this.pirorApprovalService.resubmit(data)
      .subscribe(
        req => {
          // this.loadData();
          if (req['status']) {
            this.spinner.hide();
            this.resubmitSuccess = true;
            this.resubmitSuccessMessage = req['message'];
            this.alertService.success(req['message']);
            this.router.navigate(['/dashboard/home']);
            return false;
          } else {
            this.loadCompanyAddress();
            this.resubmitSuccess = false;
            this.resubmitSuccessMessage = '';
            alert(req['message']);
          }
        },
        error => {
          this.spinner.hide();
          this.resubmitSuccess = false;
          this.resubmitSuccessMessage = '';
          console.log(error);
        }
      );

  }

  pay() {

    this.paymentItems.push(
      {
        fee_type: 'PAYMENT_PRIOR_APPROVAL',
        description: 'Prior Approval Payment',
        quantity: 1,
      }

    );

    const buy: IBuy = {
      module_type: 'PRIOR_APPROVAL_ACCOUNTS',
      module_id: this.requestId.toString(),
      description: 'Payment Module for Prior approval',
      item: this.paymentItems,
      extraPay: null
    };

    this.crToken.getCrToken(buy).subscribe(
      req => {
        this.cipher_message = req.token;
        this.payConfirm = true;
      },
      error => {
        alert(error);
        this.payConfirm = false;
      }
    );

  }

  fileChange(event, fileNane, fileDBID  ) {

    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      // tslint:disable-next-line:prefer-const
      let file: File = fileList[0];

     // console.log(fileList[0]);

      // tslint:disable-next-line:prefer-const
      let fileSize = fileList[0].size;

      if (fileSize >= 1024 * 1024 * 10) { // 4mb restriction
        alert('You can upload document only up to 10 MB');
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile', file, file.name);
      formData.append('fileName', this.slugify(fileNane));
      formData.append('fileRealName', file.name );
      formData.append('fileTypeId', fileDBID);
      formData.append('company_id',  this.companyId );
      let request_id = this.requestId.toString();
      formData.append('request_id', request_id);
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.uploadDocsURL();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (response: any) => {
            console.log('testttt');
            // this.other_doc_name = '';
            this.loadCompanyAddress();
            // this.spinner.hide();
          },
          error => {
            this.loadCompanyAddress();
            console.log('erorr');
            console.log(error);
            this.spinner.hide();
          }
        );
    }

  }

  uploadOtherResumittedDoc(event, multiple_id) {


    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      this.spinner.show();

      for (let i = 0; i < fileList.length; i++) {

        // tslint:disable-next-line:prefer-const
        let file: File = fileList[i];

        // console.log(fileList[0]);

        // tslint:disable-next-line:prefer-const
        let fileSize = fileList[i].size;

        if (fileSize >= 1024 * 1024 * 4) { // 4mb restriction
          alert('You can upload document only up to 4 MB');
          this.spinner.hide();
          return false;
        }

        // tslint:disable-next-line:prefer-const
        let formData: FormData = new FormData();
        formData.append('uploadFile', file, file.name);
        formData.append('multiple_id', multiple_id);
        formData.append('company_id', this.companyId);
        let request_id = this.requestId.toString();
        formData.append('request_id', request_id);
        // tslint:disable-next-line:prefer-const
        let headers = new HttpHeaders();
        headers.append('Content-Type', 'multipart/form-data');
        headers.append('Accept', 'application/json');

        // tslint:disable-next-line:prefer-const
        let uploadurl = this.url.uploadOtherResubmittedDocsURL();

        this.httpClient.post(uploadurl, formData, { headers: headers })
          .subscribe(
            (data: any) => {
              this.other_doc_name = '';
              this.loadCompanyAddress();
              // this.spinner.hide();
            },
            error => {
              console.log(error);
              this.spinner.hide();
            }
          );


      }


    }

  }
}
