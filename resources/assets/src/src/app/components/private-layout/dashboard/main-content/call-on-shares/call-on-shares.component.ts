import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { APICallOnShareConnection } from './services/connections/APICallOnShareConnection';
import { count, distinct } from 'rxjs/operators';
import { GeneralService } from '../../../../../http/services/general.service';
import { HelperService } from '../../../../../http/shared/helper.service';
import { DataService } from '../../../../../storage/data.service';
import { PaymentService } from '../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../http/models/payment';
import { isNull } from '@angular/compiler/src/output/output_ast';
import { CallOnShareService } from './services/callonshare.service';
import { environment } from '../../../../../../environments/environment';
import { IDownloadDocs, IUploadDocs, IshareholderItems, ICallShares, ICallShare, ISignedStakeholder} from './models/callOnShare.model';
import { IcompanyType, IcompanyInfo, IloginUser, IloginUserAddress } from '../../../../../http/models/incorporation.model';
@Component({
  selector: 'app-call-on-shares',
  templateUrl: './call-on-shares.component.html',
  styleUrls: ['./call-on-shares.component.scss']
})
export class CallOnSharesComponent implements OnInit, AfterViewInit {

  url: APICallOnShareConnection = new APICallOnShareConnection();

cipher_message: string;
paymentItems: Array<Item> = [];
paymentGateway: string = environment.paymentGateway;


signedDirectors: Array<ISignedStakeholder> = [];
signedSecs: Array<ISignedStakeholder> = [];
signedSecFirms: Array<ISignedStakeholder> = [];

formattedTodayValue = '';

payConfirm = false;
form7_payment = 0;
vat = 0;
vatVal = 0;

companyRegNumber = '';

other_tax = 0;
other_taxVal = 0;

court_order_valid = false;

court_status = '';
court_date = '';
court_name = '';
court_case_no = '';
court_penalty = '';
court_period = '';
court_discharged = '';

penalty = 0;

convinienceFee = 0;
convinienceFeeVal = 0;

total_wihtout_vat_tax = 0;
total_with_vat_tax = 0;
  // company id
  companyId: string;
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

  other_doc_name = '';
  document_confirm = false;

  progress = {

    stepArr: [
      { label: 'Company Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Shares', icon: 'fas fa-share', status: '' },
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
  loginUserInfo: IloginUser;  loginUserAddress: IloginUserAddress;
  isGuarantyCompany = false;
  isShareholderLable = 'Shareholder';
  docList: IDownloadDocs = { docs: [] };
  uploadList: IUploadDocs = { docs: [] };
  uploadOtherList: IUploadDocs = {docs: [] };

  uploadedList: {};
  uploadedListArrWithToken: {};

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

  shareholdersList: IshareholderItems = { sh: []};
  shareholderFirmList: IshareholderItems = { sh: []};


  callShareList: ICallShares = {share: [] };
  public call: ICallShare = {id: null, showEditPane: 0, shareholder_id: '', shareholder_other_name: '', shareholder_type: 'natural' , share_prior_to_this_call: '', value_respect_of_share: '', name_of_shares: '', value_respect_of_total_share: '', date_of_performance: ''};

  share_records_already_exists = true;

  stated_capital = 0;
  signing_party_designation = '';
  singning_party_name = '';


  constructor(
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private callShareService: CallOnShareService,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private general: GeneralService,
    private helper: HelperService,
    public pdc: DataService,
    private crToken: PaymentService,
    private iBy: IBuyDetails
    ) {
    this.companyId = route.snapshot.paramMap.get('companyId');
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
      loginUser: this.loginUserEmail
    };
    this.spinner.show();

    // load Company data from the server
    this.callShareService.callOnShareData(data)
      .subscribe(
        req => {

          if ( req['data']['createrValid'] === false ) {

            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          this.moduleStatus = req['data']['moduleStatus'];

          if ( !( this.moduleStatus === 'CALLS_ON_SHARES_PROCESSING' || this.moduleStatus === 'CALLS_ON_SHARES_RESUBMIT' ) ) {
            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          this.formattedTodayValue = this.getFormatedToday();

          // tslint:disable-next-line:radix
          this.requestId = req['data']['request_id'] ? parseInt( req['data']['request_id'] ) : 0;

          this.compayType = req['data']['companyType'];
          this.processStatus = req['data']['processStatus'];
          this.postFixEn = req['data']['postfix'];
          this.postFixSi = req['data']['postfix_si'];
          this.postFixTa = req['data']['postfix_ta'];
          this.companyRegNumber = req['data']['certificate_no'];


          this.companyInfo = req['data']['companyInfo'];
          this.loginUserInfo = req['data']['loginUser'];
          this.loginUserAddress = req['data']['loginUserAddress'];

          this.signedDirectors = req['data']['directors'];
          this.signedSecs = req['data']['secs'];
          this.signedSecFirms = req['data']['sec_firms'];


          this.callShareList.share = req['data']['share_calls'];
          this.shareholdersList.sh = req['data']['shareholders'];
          this.shareholderFirmList.sh = req['data']['shareholder_firms'];
          this.docList = req['data']['downloadDocs'];
          this.uploadList = req['data']['uploadDocs'];
          this.allFilesUploaded = this.uploadList['uploadedAll'];

          this.uploadOtherList = req['data']['uploadOtherDocs'];

          this.externalGlobComment = req['data']['external_global_comment'];

          this.stated_capital = req['data']['callonSharesRecord']['stated_capital'];
          this.signing_party_designation =  req['data']['callonSharesRecord']['signing_party_designation'];
          this.singning_party_name =  req['data']['callonSharesRecord']['signed_party_id'];

          this.court_status = req['data']['court_data']['court_status'];
          this.court_name = req['data']['court_data']['court_name'];
          this.court_date = req['data']['court_data']['court_date'];
          this.court_case_no = req['data']['court_data']['court_case_no'];
          this.court_discharged = req['data']['court_data']['court_discharged'];
          this.court_penalty = req['data']['court_data']['court_penalty'];
          this.court_period = req['data']['court_data']['court_period'];

          this.form7_payment = (req['data']['form7_payment']) ? parseFloat( req['data']['form7_payment'] ) : 0;
          this.vat = (req['data']['vat']) ? parseFloat( req['data']['vat'] ) : 0;
          this.other_tax = (req['data']['other_tax']) ? parseFloat( req['data']['other_tax'] ) : 0;
          this.convinienceFee = (req['data']['convinienceFee']) ? parseFloat( req['data']['convinienceFee'] ) : 0;

          this.penalty =  parseFloat(req['data']['penalty_value']);

          let penalty_recheck = this.court_status === 'yes' ? 0 : this.penalty;
          this.total_wihtout_vat_tax = this.form7_payment + (penalty_recheck);

          this.other_taxVal = ( this.total_wihtout_vat_tax * this.other_tax ) / 100;
          this.vatVal = ( this.total_wihtout_vat_tax + this.other_taxVal ) * this.vat / 100;
          this.convinienceFeeVal = ( this.total_wihtout_vat_tax + this.other_taxVal  + this.vatVal ) * this.convinienceFee / 100;
          this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;

          this.changeProgressStatuses(this.stepOn);

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

  fileChange(event, fileNane, fileDBID  ) {

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
      formData.append('fileRealName', file.name );
      formData.append('fileTypeId', fileDBID);
      formData.append('company_id',  this.companyId );
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
            if ( data['error'] === 'no' ) {
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

  uploadOtherDoc(event, fileNane, fileDBID  ) {


    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      this.spinner.show();

     for (let i = 0; i < fileList.length; i++ ) {

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
      formData.append('fileName', this.slugify(fileNane));
      let filename = (this.other_doc_name) ?  this.other_doc_name + '.pdf' : file.name;
      formData.append('fileRealName', filename );
      formData.append('fileDescription', this.other_doc_name);
      formData.append('fileTypeId', fileDBID);
      formData.append('company_id', this.companyId );
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.uploadOtherDocsURL();

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


   uploadOtherResumittedDoc(event, multiple_id  ) {


    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      this.spinner.show();

     for (let i = 0; i < fileList.length; i++ ) {

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
      formData.append('company_id', this.companyId );
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


  removeDoc( docTypeId ) {

    let removeConf = confirm('Are you sure, you want to delete uploaded document ?');

    if (!removeConf) {
      return false;
    }

    const data = {
      companyId: this.companyId,
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

  removeOtherDoc(token){
    const data = {
      file_token: token ,
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


showToggleCallRecord(recId = 0) {

  // tslint:disable-next-line:prefer-const
  for (let i in this.callShareList.share) {
    if (this.callShareList.share[i]['id'] === recId) {
      this.callShareList.share[i]['showEditPane'] = this.callShareList.share[i]['showEditPane'] === recId ? null : recId;
      return true;
    }
  }
}

resetCallRecord() {
  this.call = {id: null, showEditPane: 0, shareholder_id: '', shareholder_other_name: '', shareholder_type: 'natural' , share_prior_to_this_call: '', value_respect_of_share: '', name_of_shares: '', value_respect_of_total_share: '', date_of_performance: '' };
}



validateCallRecordEdit(i) {

  let row = this.callShareList.share[i];
  if (!
    (
      // tslint:disable-next-line:radix
      ( row.shareholder_type === 'natural' || row.shareholder_type === 'firm' ) &&
        // tslint:disable-next-line:radix
        row.shareholder_id && parseInt(row.shareholder_id) &&
        ( row.shareholder_id === '999999' ? row.shareholder_other_name : true ) &&
        row.share_prior_to_this_call &&
        row.name_of_shares && parseFloat(row.name_of_shares) &&
        row.value_respect_of_share && parseFloat(row.value_respect_of_share) &&
        row.value_respect_of_total_share && parseFloat(row.value_respect_of_total_share) &&
        row.date_of_performance


    )
  ) {
    this.shareRecordValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
    this.enableStep2Submission = false;
    this.enableStep2SubmissionEdit = false;
    return false;
  } else {

    this.shareRecordValitionMessage = '';
    this.enableStep2Submission = true;
    this.enableStep2SubmissionEdit = true;
    return true;

  }
}


validateCallRecord() {
  if (!
    (
      // tslint:disable-next-line:radix
      ( this.call.shareholder_type === 'natural' || this.call.shareholder_type === 'firm' ) &&
        // tslint:disable-next-line:radix
        this.call.shareholder_id && parseInt(this.call.shareholder_id) &&
        ( this.call.shareholder_id === '999999' ? this.call.shareholder_other_name : true ) &&
        this.call.share_prior_to_this_call &&
        this.call.name_of_shares && parseFloat(this.call.name_of_shares) &&
        this.call.value_respect_of_share && parseFloat(this.call.value_respect_of_share) &&
        this.call.value_respect_of_total_share && parseFloat(this.call.value_respect_of_total_share) &&
        this.call.date_of_performance

    )
  ) {
    this.shareRecordValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
    this.validShareRecordItem = false;
    return false;
  } else {

    this.shareRecordValitionMessage = '';
    this.validShareRecordItem = true;
    return true;

  }
}


 saveShareRecord() {

  // tslint:disable-next-line:prefer-const
  let copy = Object.assign({}, this.call);
  this.callShareList.share.push(copy);

  // tslint:disable-next-line:max-line-length
  this.call = {id: null, showEditPane: 0, shareholder_id: '', shareholder_other_name: '' , shareholder_type: 'natural' , share_prior_to_this_call: '', value_respect_of_share: '', name_of_shares: '', value_respect_of_total_share: '', date_of_performance: ''};
  this.validShareRecordItem = false;
  this.submitShareRecord('remove');

}

submitShareRecord(action = '') {

  const data = {
    companyId: this.companyId,
    loginUser: this.loginUserEmail,
    call_records: this.callShareList,
    stated_capital: (this.stated_capital) ?  this.stated_capital.toString() : '',
    signing_party_designation: this.signing_party_designation,
    singning_party_name: this.singning_party_name
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

removeShareRecord(i: number, recId: number = 0) {

  if ( !confirm('Are you sure you want to remove this record?') ) {
    return true;
  }

  this.callShareList.share.splice(i, 1);
  if (!recId) {
    return true;
  }
  this.submitShareRecord('remove');

}


/**************End SHARE Functions*******************/


  pay() {


    this.paymentItems.push(
      {
          fee_type: 'PAYMENT_CALLS_ON_SHARES_FORM7',
          description: 'Calls on Shares Record',
          quantity: 1,
      }

    );

    const buy: IBuy = {
      module_type: 'MODULE_CALLS_ON_SHARES',
      module_id: this.requestId.toString(),
      description: 'Calls on Shares',
      item: this.paymentItems,
      extraPay: null,
      penalty: ( this.court_status !== 'yes') ? this.penalty.toString() : null
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
      court_discharged: this.court_discharged
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


  resubmit() {
    const data = {
      companyId: this.companyId,
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
          }else {
            this.loadData();
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


