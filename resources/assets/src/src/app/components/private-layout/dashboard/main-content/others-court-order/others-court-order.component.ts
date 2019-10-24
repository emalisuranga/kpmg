import { Component, OnInit, AfterViewInit } from '@angular/core';
import { DataService } from '../../../../../storage/data.service';
import { GeneralService } from '../../../../../http/services/general.service';
import { BalanceSheetdateService } from '../../../../../http/services/balance-sheetdate.service';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { NgxSpinnerService } from 'ngx-spinner';
import { HelperService } from '../../../../../http/shared/helper.service';
import { PaymentService } from '../../../../../http/services/payment.service';
import { Item, IBuy } from '../../../../../http/models/payment';
import { Router, ActivatedRoute } from '@angular/router';
import { CalculationService } from './../../../../../http/shared/calculation.service';
import * as $ from 'jquery';
import { environment } from '../../../../../../environments/environment';
import { OtherCourtService } from './service/other-court.service';
import { IcompanyType, IcompanyInfo, IloginUser, IloginUserAddress } from '../../../../../http/models/incorporation.model';
import { IUploadDocs } from './models/otherscourdorder.model';
import { APIothersCourtConnection } from './service/APIothersCourtConnection';
import { AlertService } from 'ngx-alerts';


@Component({
  selector: 'app-others-court-order',
  templateUrl: './others-court-order.component.html',
  styleUrls: ['./others-court-order.component.scss']
})
export class OthersCourtOrderComponent implements OnInit, AfterViewInit {

  url: APIothersCourtConnection = new APIothersCourtConnection();

  progress = {

    stepArr: [
      { label: 'Other Court Order Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },

    ],

    progressPercentage: '18%'
  };

  mainDoc_payment = 0;
  doc_payment = 0;
  total_wihtout_vat_tax = 0;
  total_with_vat_tax = 0;
  vat = 0;
  vatVal = 0;
  other_tax = 0;
  other_taxVal = 0;
  convinienceFee = 0;
  convinienceFeeVal = 0;
    // company id
  companyId = '';
  changeId: string;
  oldaddressId: string;
  companyName: string;
  companyRegno: string;
  requestId = '';
  reqStatus: string;
  loginUserEmail: string;

  // process status
  processStatus: string;
  annualReturnStatus = '';
  resubmitSuccess = false;
  resubmitSuccessMessage = '';
  stepOn = 0;
  externalGlobComment = '';
  requestNumber = '';

  moduleStatus = '';
  formattedTodayValue = '';
  currencies = [];
  other_doc_name = '';
  doc_name = '';
  additional = [];
  companyRegNumber = '';
  allFilesUploaded = false;

  cipher_message: string;
  paymentItems: Array<Item> = [];
  paymentGateway: string = environment.paymentGateway;

  payConfirm = false;

  companyInfo: IcompanyInfo = {
    abbreviation_desc: '', address_id: null, created_at: null, created_by: null, email: '', id: null, name: '', name_si: '', name_ta: '', postfix: '', status: null, type_id: null, updated_at: null, objective1: null, objective2: null, objective3: null, objective4: null, objective5: null, otherObjective: '', incorporation_at: ''
  };

  compayType: IcompanyType = { key: '', value: '', id: null, value_si: '', value_ta: '' };

   uploadList: IUploadDocs = { docs: [] };
   uploadOtherList: IUploadDocs = { docs: [] };

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
    private bsdService: BalanceSheetdateService,
    private otherCourtService: OtherCourtService,
    private alertService: AlertService
  ) {
    // this.companyId = JSON.parse(localStorage.getItem('otherCompanyId'));
    this.companyId = route.snapshot.paramMap.get('companyId');
    this.loginUserEmail = localStorage.getItem('currentUser');
    this.requestId = route.snapshot.paramMap.get('requestId');
    this.reqStatus = route.snapshot.paramMap.get('status');

    this.loadCompanyAddress();
  }

  ngOnInit() {
  }

  changeProgressStatuses(newStatus = 0) {
    this.stepOn = newStatus;
    // console.log(this.stepOn);
    // this.formattedTodayValue = this.getFormatedToday();

    this.progress.progressPercentage = (this.stepOn > 1) ? (84) + '%' : (18 + this.stepOn * 29) + '%' ;

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
      companyId: this.companyId,
      loginUser: this.loginUserEmail,
      requestId: this.requestId,
      reqStatus: this.reqStatus
    };
    this.spinner.show();
    this.otherCourtService.loadCompanyAddress(data)
      .subscribe(
        req => {
           this.moduleStatus = req['data']['moduleStatus'];

          if (!(this.moduleStatus === 'OTHERS_COURT_ORDER_PROCESSING' || this.moduleStatus === 'OTHERS_COURT_ORDER_RESUBMIT')) {
            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }
          // tslint:disable-next-line:radix
          this.requestId = req['data']['request_id'] ? req['data']['request_id'] : '';

          this.compayType = req['data']['companyType'];
          this.processStatus = req['data']['processStatus'];
          this.currencies = req['data']['currencies'];
          this.companyRegNumber = req['data']['certificate_no'];
          this.companyInfo = req['data']['companyInfo'];
          this.uploadList = req['data']['uploadDocs'];
          this.uploadOtherList = req['data']['uploadOtherDocs'];
          this.allFilesUploaded = this.uploadList['uploadedAll'];
          this.doc_payment = (req['data']['othersCourt_payment']) ? parseFloat(req['data']['othersCourt_payment']) : 0;
          if (this.uploadList['file_count'] === 0){
            this.mainDoc_payment = this.doc_payment;
          }else {
            this.mainDoc_payment = this.uploadList['file_count'] * this.doc_payment;
          }
          this.total_wihtout_vat_tax = this.mainDoc_payment;
          this.vat = (req['data']['vat']) ? parseFloat(req['data']['vat']) : 0;
          this.other_tax = (req['data']['other_tax']) ? parseFloat(req['data']['other_tax']) : 0;
          this.convinienceFee = (req['data']['convinienceFee']) ? parseFloat(req['data']['convinienceFee']) : 0;
          this.other_taxVal = (this.total_wihtout_vat_tax * this.other_tax) / 100;
          this.vatVal = (this.total_wihtout_vat_tax + this.other_taxVal) * this.vat / 100;
          this.convinienceFeeVal = (this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal) * this.convinienceFee / 100;
          this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;
          this.externalGlobComment = req['data']['external_global_comment'];
          this.spinner.hide();
          console.log(this.doc_payment);
          console.log(this.uploadList['file_count']);
          console.log(this.mainDoc_payment);
        },
        error => {
          alert(error);
          this.spinner.hide();
        }
      );


    // this.changeProgressStatuses(this.stepOn);
  }

  private slugify(text) {
    return text.toString().toLowerCase()
      .replace(/\s+/g, '-')           // Replace spaces with -
      .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
      .replace(/\-\-+/g, '-')         // Replace multiple - with single -
      .replace(/^-+/, '')             // Trim - from start of text
      .replace(/-+$/, '');            // Trim - from end of text
  }

  fileChange(event, fileNane, fileDBID) {

    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      // tslint:disable-next-line:prefer-const
      let file: File = fileList[0];

      // console.log('file uplode');

      // tslint:disable-next-line:prefer-const
      let fileSize = fileList[0].size;

      if (fileSize >= 1024 * 1024 * 10) { // 4mb restriction
        alert('You can upload document only up to 10 MB');
        event.target.value = '';
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile', file, file.name);
      formData.append('fileName', this.slugify(fileNane));
      let filename = (this.doc_name) ? this.doc_name + '.pdf' : file.name;
      formData.append('fileRealName', filename);
      formData.append('fileDescription', this.doc_name);
      formData.append('fileTypeId', fileDBID);
      formData.append('company_id', this.companyId);
      formData.append('requestNumber', this.requestId);
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.uploadDocsURL();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            if (data['error'] === 'no') {
              //  this.uploadList = data['uploadDocs'];
              //  this.uploadedList = data['uploadedList'];
              //  this.uploadedListArrWithToken = data['uploadedListArrWithToken'];
            }
            this.doc_name = '';
            this.loadCompanyAddress();
            event.target.value = '';
            this.allFilesUploaded = this.uploadList['uploadedAll'];
            // this.spinner.hide();
          },
          error => {
            console.log(error);
            event.target.value = '';
            this.spinner.hide();
          }
        );
    }

  }

  removeDoc(docTypeId) {

    let removeConf = confirm('Are you sure, you want to delete uploaded document ?');

    if (!removeConf) {
      return false;
    }

    const data = {
      companyId: this.companyId,
      fileTypeId: docTypeId,
    };
    this.spinner.show();
    this.otherCourtService.removeDoc(data)
      .subscribe(
        rq => {
          this.loadCompanyAddress();
          //   this.spinner.hide();
        },
        error => {
          this.spinner.hide();
          this.loadCompanyAddress();
          console.log(error);
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
          event.target.value = '';
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
        formData.append('requestNumber', this.requestId);
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
              event.target.value = '';
               this.spinner.hide();
            },
            error => {
              console.log(error);
              event.target.value = '';
              this.spinner.hide();
            }
          );


      }


    }

  }

  removeOtherDoc(token){
    const data = {
      file_token: token ,
    };
    this.spinner.show();

    // load Company data from the server
    this.otherCourtService.removeOtherDoc(data)
      .subscribe(
        req => {
          this.loadCompanyAddress();
        }
      );

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

  uploadResumittedDoc(event, multiple_id, fileName) {


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
        formData.append('fileName', fileName);
        // tslint:disable-next-line:prefer-const
        let headers = new HttpHeaders();
        headers.append('Content-Type', 'multipart/form-data');
        headers.append('Accept', 'application/json');

        // tslint:disable-next-line:prefer-const
        let uploadurl = this.url.uploadResubmittedDocsURL();

        this.httpClient.post(uploadurl, formData, { headers: headers })
          .subscribe(
            (data: any) => {
              this.doc_name = '';
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

  pay() {

    this.paymentItems.push(
      {
          fee_type: 'PAYMENT_OTHERS_COURT_ORDER',
        description: 'Others Court Order Payment',
          quantity: 1,
      }

    );

    const buy: IBuy = {
      module_type: 'MODULE_OTHERS_COURT_ACCOUNTS',
      module_id: this.requestId.toString(),
      description: 'Others Court Order accounts',
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

  resubmit() {
    const data = {
      companyId: this.companyId,
    };
    this.spinner.show();

    this.otherCourtService.resubmit(data)
      .subscribe(
        req => {
         // this.loadData();
          if (req['status']) {
            this.spinner.hide();
            this.resubmitSuccess = true;
            this.resubmitSuccessMessage = req['message'];
            return false;
          }else {
            this.loadCompanyAddress();
            this.resubmitSuccess = false;
            this.resubmitSuccessMessage = '';
            alert( req['message']);
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

  resubmitMessageClick(){
    this.spinner.hide();
    this.router.navigate(['/dashboard/home']);
    return false;
  }



}
