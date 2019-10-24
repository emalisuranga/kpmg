import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { APIForm37Connection } from './services/connections/APIForm37Connection';
import { count, distinct } from 'rxjs/operators';
import { GeneralService } from '../../../../../http/services/general.service';
import { HelperService } from '../../../../../http/shared/helper.service';
import { DataService } from '../../../../../storage/data.service';
import { PaymentService } from '../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../http/models/payment';
import { isNull } from '@angular/compiler/src/output/output_ast';
import { Form37Service } from './services/form37.service';
import { environment } from '../../../../../../environments/environment';
import { IDownloadDocs, IUploadDocs, IshareholderItems, ICallShares, ICallShare, IRegisterChargeRecords, IRegisterChargeRecord, ICharges, IChargeTypes, INameChangeOverseasRecord } from './models/form37Model';
import { IcompanyType, IcompanyInfo, IloginUser, IloginUserAddress } from '../../../../../http/models/incorporation.model';
@Component({
  selector: 'app-notice-of-change-name-of-overseas',
  templateUrl: './notice-of-change-name-of-overseas.component.html',
  styleUrls: ['./notice-of-change-name-of-overseas.component.scss']
})
export class NoticeOfChangeNameOfOverseasComponent implements OnInit, AfterViewInit {

  url: APIForm37Connection = new APIForm37Connection();

  cipher_message: string;
  paymentItems: Array<Item> = [];
  paymentGateway: string = environment.paymentGateway;

  payConfirm = false;
  form37b_payment = 0;
  vat = 0;
  vatVal = 0;

  formattedTodayValue = '';

  other_tax = 0;
  other_taxVal = 0;

  convinienceFee = 0;
  convinienceFeeVal = 0;

  total_wihtout_vat_tax = 0;
  total_with_vat_tax = 0;
  // company id
  companyId: string;
  changeId: string;
  requestId: number;
  loginUserEmail: string;
  // process status
  processStatus: string;
  annualReturnStatus = '';
  resubmitSuccess = false;
  resubmitSuccessMessage = '';
  stepOn = 0;
  externalGlobComment = '';

  moduleStatus = '';

  court_order_valid = false;

  court_status = '';
  court_date = '';
  court_name = '';
  court_case_no = '';
  court_penalty = '';
  court_period = '';
  court_discharged = '';

  document_confirm = false;

  penalty = 0;

  progress = {

    stepArr: [
      { label: 'Company Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Name Change Information', icon: 'fas fa-info-circle', status: '' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Pay and Complete', icon: 'fa fa-money-bill-alt', status: '' },
    ],

    progressPercentage: '10%'

  };

  compayType: IcompanyType = { key: '', value: '', id: null, value_si: '', value_ta: '' };

  postFixEn = ''; postFixSi = ''; postFixTa = '';

  companyInfo: IcompanyInfo = {
    abbreviation_desc: '', address_id: null, created_at: null, created_by: null, email: '', id: null, name: '', name_si: '', name_ta: '', postfix: '', status: null, type_id: null, updated_at: null, objective1: null, objective2: null, objective3: null, objective4: null, objective5: null, otherObjective: '', incorporation_at: ''
  };

  companyRegistrationNumber = '';
  loginUserInfo: IloginUser; loginUserAddress: IloginUserAddress;
  isGuarantyCompany = false;
  isShareholderLable = 'Shareholder';
  docList: IDownloadDocs = { docs: [] };
  uploadOtherList: IUploadDocs = { docs: [] };
  uploadList: IUploadDocs = { docs: [] };

  uploadedList: {};
  uploadedListArrWithToken: {};
  other_doc_name = '';
  companyRegNumber = '';
  companyMember = '';
  auth_person_name = [];
  other_name = '';
  other_person = false;


  enableStep1Submission = false;
  directorValitionMessage = '';
  directorAlreadyExistMessage = '';
  secAlreadyExistMessage = '';
  shAlreadyExistMessage = '';

  shareRegisterValitionMessage = '';
  annualRecordValitionMessage = '';
  annualAuditorRecordValitionMessage = '';
  annualChargeRecordValitionMessage = '';
  shareRecordValitionMessage = '';

  enableStep2Submission = true;
  enableStep2SubmissionEdit = true;

  designationValidationRule = '';

  validDirector = false;
  shValitionMessage = '';
  validSh = false;
  secValitionMessage = '';
  validSec = false;
  guarantee_sec_err_happend = false;
  validateShBenifFlag = false;
  validateSecShBenifFlag = false;
  directorNicLoaded = false;
  directorPassportLoaded = false;
  secNicLoaded = false;
  shNicLoaded = false;
  secNicLoadedEdit = -1;
  validateUploadeStatusFlag = false;
  loadPDCcompany = false;

  validShareRegister = false;
  validAnnualRecord = false;
  validAnnualAuditorRecord = false;
  validAnnualChargeRecord = false;
  validShareRecordItem = false;

  allFilesUploaded = false;

  shareholdersList: IshareholderItems = { sh: [] };
  shareholderFirmList: IshareholderItems = { sh: [] };


  share_records_already_exists = true;



  public charge: INameChangeOverseasRecord = { id: null, new_name: '', auth_person_name: '', date_of_change: '' };

  constructor(
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private callShareService: Form37Service,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private general: GeneralService,
    private helper: HelperService,
    public pdc: DataService,
    private crToken: PaymentService,
    private iBy: IBuyDetails
  ) {
    this.companyId = route.snapshot.paramMap.get('companyId');
    this.changeId = route.snapshot.paramMap.get('changeId');
    this.loginUserEmail = localStorage.getItem('currentUser');

    this.loadData();

  }

  sanitize(url: string) {
    return this.sanitizer.bypassSecurityTrustUrl(url);
  }

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

  ngOnInit() {

    // this.spinner.show();

  }

  changeProgressStatuses(newStatus = 0) {
    this.stepOn = newStatus;

    this.formattedTodayValue = this.getFormatedToday();

    this.progress.progressPercentage = (this.stepOn >= 2) ? (10 * 2 + this.stepOn * 20) + '%' : (10 + this.stepOn * 20) + '%';

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
  private loadData() {
    const data = {
      companyId: this.companyId,
      loginUser: this.loginUserEmail,
      changeId: this.changeId
    };
    this.spinner.show();

    // load Company data from the server
    this.callShareService.callOnShareData(data)
      .subscribe(
        req => {

          if (req['data']['createrValid'] === false) {

            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          this.moduleStatus = req['data']['moduleStatus'];

          if (!(this.moduleStatus === 'OVERSEAS_NAME_CHANGE_PROCESSING' || this.moduleStatus === 'OVERSEAS_NAME_CHANGE_RESUBMIT')) {
            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          this.formattedTodayValue = this.getFormatedToday();

          // tslint:disable-next-line:radix
          this.requestId = req['data']['request_id'] ? parseInt(req['data']['request_id']) : 0;

          this.compayType = req['data']['companyType'];
          this.processStatus = req['data']['processStatus'];
          this.postFixEn = req['data']['postfix'];
          this.postFixSi = req['data']['postfix_si'];
          this.postFixTa = req['data']['postfix_ta'];
          this.companyRegNumber = req['data']['certificate_no'];


          this.companyInfo = req['data']['companyInfo'];
          this.loginUserInfo = req['data']['loginUser'];
          this.loginUserAddress = req['data']['loginUserAddress'];
          this.externalGlobComment = req['data']['external_global_comment'];


          if (req['data']['record']) {

            this.charge.new_name = (req['data']['record']['new_name'])  ?  req['data']['record']['new_name'] : req['data']['newCompanyName'];
            this.auth_person_name = req['data']['record']['auth_person_name'] ? JSON.parse(req['data']['record']['auth_person_name']) : [];
            this.other_name = req['data']['record']['other_auth_person'];
            this.charge.date_of_change = req['data']['record']['date_of_change'];
          }

          this.docList = req['data']['downloadDocs'];
          this.uploadOtherList = req['data']['uploadOtherDocs'];
          this.uploadList = req['data']['uploadDocs'];
          this.allFilesUploaded = this.uploadList['uploadedAll'];
          this.companyMember = req['data']['companyMember'];

          this.court_status = req['data']['court_data']['court_status'];
          this.court_name = req['data']['court_data']['court_name'];
          this.court_date = req['data']['court_data']['court_date'];
          this.court_case_no = req['data']['court_data']['court_case_no'];
          this.court_discharged = req['data']['court_data']['court_discharged'];
          this.court_penalty = req['data']['court_data']['court_penalty'];
          this.court_period = req['data']['court_data']['court_period'];


          this.form37b_payment = (req['data']['form37b_payment']) ? parseFloat(req['data']['form37b_payment']) : 0;
          this.vat = (req['data']['vat']) ? parseFloat(req['data']['vat']) : 0;
          this.other_tax = (req['data']['other_tax']) ? parseFloat(req['data']['other_tax']) : 0;
          this.convinienceFee = (req['data']['convinienceFee']) ? parseFloat(req['data']['convinienceFee']) : 0;

          this.total_wihtout_vat_tax = this.form37b_payment;

          this.penalty =  parseFloat(req['data']['penalty']);

          let penalty_recheck = this.court_status === 'yes' ? 0 : this.penalty;
          this.total_wihtout_vat_tax = this.form37b_payment + (penalty_recheck);

          this.other_taxVal = (this.total_wihtout_vat_tax * this.other_tax) / 100;
          this.vatVal = (this.total_wihtout_vat_tax + this.other_taxVal) * this.vat / 100;
          this.convinienceFeeVal = (this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal) * this.convinienceFee / 100;
          this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;

          this.changeProgressStatuses(this.stepOn);

          this.validateCharge();

          this.validateCourtOrder();

          this.spinner.hide();
        }
      );



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

      // console.log(fileList[0]);

      // tslint:disable-next-line:prefer-const
      let fileSize = fileList[0].size;

      if (fileSize > 1024 * 1024 * 4) { // 4mb restriction
        alert('File size should be less than 4 MB');
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile', file, file.name);
      formData.append('fileName', this.slugify(fileNane));
      formData.append('fileRealName', file.name);
      formData.append('fileTypeId', fileDBID);
      formData.append('company_id', this.companyId);
      formData.append('changeId', this.changeId);
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
            this.loadData();
            this.allFilesUploaded = this.uploadList['uploadedAll'];
            // this.spinner.hide();
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }

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




  /************** Functions************************/


  submitRecord(action = '') {

    let auth_person: string = JSON.stringify(this.auth_person_name);

    const data = {
      companyId: this.companyId,
      changeId: this.changeId,
      loginUser: this.loginUserEmail,
      overseas_name_change: this.charge,
      auth_person_name: auth_person,
      auth_person_other_name: this.other_name,
    };

    this.callShareService.shareCallSubmit(data)
      .subscribe(
        req => {
          this.loadData();
          if (action === 'remove') {
            this.changeProgressStatuses(1);
            return false;
          }
          this.changeProgressStatuses(2);
        },
        error => {
          this.changeProgressStatuses(2);
          console.log(error);
        }

      );

  }




  /**************End SHARE Functions*******************/


  pay() {

    this.paymentItems.push(
      {
        fee_type: 'PAYMENT_OVERSEAS_NAME_CHANGE_FORM_37B',
        description: 'Name Change Notice of Overseas Company Record',
        quantity: 1,
      }

    );

    const buy: IBuy = {
      module_type: 'MODULE_OVERSEAS_NAME_CHANGE',
      module_id: this.requestId.toString(),
      description: 'Name Change Notice of Overseas Company',
      item: this.paymentItems,
      penalty: ( this.court_status !== 'yes') ? this.penalty.toString() : null,
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
      changeId: this.changeId
    };
    this.spinner.show();

    this.callShareService.resubmit(data)
      .subscribe(
        req => {
          // this.loadData();
          if (req['status']) {
            this.spinner.hide();
            this.resubmitSuccess = true;
            this.resubmitSuccessMessage = req['message'];
            return false;
          } else {
            this.loadData();
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

  resubmitMessageClick() {
    this.spinner.hide();
    this.router.navigate(['/dashboard/home']);
    return false;
  }


  /************************ */

   in_array_other(){

    if (!this.auth_person_name.length) {
      return false;
    }
    for (let i = 0; i < this.auth_person_name.length; i++ ) {
      if ( this.auth_person_name[i] === 'Other' ) {
        return true;
      }
    }

    return false;

  }

  validateCharge() {

   if (
    this.charge.new_name &&
    this.charge.date_of_change &&
    this.auth_person_name.length &&
    ( this.in_array_other() ? this.other_name : true)

   ) {
    this.shareRecordValitionMessage = '';
    this.validShareRecordItem = true;
    return true;
   } else {
    this.shareRecordValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
    this.validShareRecordItem = false;
    return false;
   }

  }

  removeDoc(docTypeId) {

    let removeConf = confirm('Are you sure, you want to delete uploaded document ?');

    if (!removeConf) {
      return false;
    }

    const data = {
      companyId: this.companyId,
      changeId: this.changeId,
      fileTypeId: docTypeId,
    };
    this.spinner.show();
    this.callShareService.removeDoc(data)
      .subscribe(
        rq => {
          this.loadData();
          //   this.spinner.hide();
        },
        error => {
          this.spinner.hide();
          this.loadData();
          console.log(error);
        }

      );


  }

  removeOtherDoc(token) {
    const data = {
      file_token: token,
    };
    this.spinner.show();

    // load Company data from the server
    this.callShareService.removeOtherDoc(data)
      .subscribe(
        req => {
          this.loadData();
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
        formData.append('changeId', this.changeId);
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
              this.loadData();
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
        formData.append('changeId', this.changeId);
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
              this.loadData();
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

  validateCourtOrder() {

    if (this.penalty <= 0 ) {
      this.court_order_valid = true;
      return true;
    }

     if (
        (this.court_status === 'yes' || this.court_status === 'no') &&
        (this.court_status === 'yes' ? (this.court_name && this.court_case_no && this.court_date) : true )

        ){
        this.court_order_valid = true;
     }else {
       this.court_order_valid = false;
     }
  }

  updateCourtDetails() {

    const data = {
      companyId: this.companyId,
      court_status: this.court_status,
      court_name: this.court_name,
      court_date: this.court_date,
      court_case_no: this.court_case_no,
      court_penalty: this.court_penalty,
      court_period: this.court_period,
      court_discharged: this.court_discharged,
      changeId: this.changeId
    };

    this.spinner.show();

    this.callShareService.updateCourtDetails(data)
      .subscribe(
        req => {
          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(4);

            return false;
          } else{
             alert(req['message']);
             this.court_order_valid = false;
             this.spinner.hide();
          }

        },
        error => {
          this.changeProgressStatuses(3);
          console.log(error);
          this.spinner.hide();
        }

      );

  }


  // addPoint(){
  //   const name = {name: 'hgjhgh'};
  //   this.other_name.push({ name: 'hgjhgh' });
  // }

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

}





