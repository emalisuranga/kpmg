import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { APIForm9Connection } from './services/connections/APIForm9Connection';
import { count, distinct } from 'rxjs/operators';
import { GeneralService } from '../../../../../http/services/general.service';
import { HelperService } from '../../../../../http/shared/helper.service';
import { DataService } from '../../../../../storage/data.service';
import { PaymentService } from '../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../http/models/payment';
import { isNull } from '@angular/compiler/src/output/output_ast';
import { Form9Service } from './services/form9.service';
import { environment } from '../../../../../../environments/environment';
import { IDownloadDocs, IUploadDocs, IshareholderItems, ICallShares, ICallShare, IForm9Records, IForm9Record} from './models/form9.model';
import { IcompanyType, IcompanyInfo, IloginUser, IloginUserAddress } from '../../../../../http/models/incorporation.model';
@Component({
  selector: 'app-shares-redemption-acquisition',
  templateUrl: './shares-redemption-acquisition.component.html',
  styleUrls: ['./shares-redemption-acquisition.component.scss']
})
export class SharesRedemptionAcquisitionComponent implements OnInit, AfterViewInit {

  url: APIForm9Connection = new APIForm9Connection();

cipher_message: string;
paymentItems: Array<Item> = [];
paymentGateway: string = environment.paymentGateway;

payConfirm = false;
form7_payment = 0;
vat = 0;
vatVal = 0;

other_tax = 0;
other_taxVal = 0;

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
  share_types = [];

  progress = {

    stepArr: [
      { label: 'Company Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Records', icon: 'fas fa-share', status: '' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Pay and Complete', icon: 'fa fa-money-bill-alt', status: '' },
    ],

    progressPercentage: '10%'

  };

  compayType: IcompanyType = { key: '', value: '', id: null, value_si: '', value_ta: '' };
  companyRegNumber = '';

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
  uploadOther64or67List: IUploadDocs = {docs: [] };
  uploadOther31Docs: IUploadDocs = {docs: [] };
  uploadOtherCoDocs: IUploadDocs = {docs: [] };

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
  public call: ICallShare = {id: null, showEditPane: 0, shareholder_id: '', shareholder_type: 'natural' , share_prior_to_this_call: '', value_respect_of_share: '', name_of_shares: '', value_respect_of_total_share: ''};

  form9RecordList: IForm9Records = {rec: [] };
  public rec: IForm9Record = {id: null, nic: null, passno: null, person_type: null, other_share_class: null, regno: null, showEditPane: 0, shareholder_id: '', person_name: '' , norm_type : 'Person', aquire_or_redeemed : 'acquire', aquire_or_redeemed_value: '', date: '', share_class: ''};

  share_records_already_exists = true;

  stated_capital = 0;
  signing_party_designation = '';
  singning_party_name = '';
  total_company_shares = '';
  ground: any;
  groundType: any;
  type_of_64_67: any;
  formattedTodayValue = '';
  members: any;
  nicarray: any;
  passarray: any;
  regnoarray: any;
  signbyid: any;
  convert: any;
  document_confirm = false;
  other_doc_name = '';
  other_doc64or67_name = '';
  other_doc31_name = '';
  other_docCO_name = '';

  constructor(
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private callShareService: Form9Service,
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

  private validateNIC(nic) {
    if (!nic) {
      return true;
    }
    // tslint:disable-next-line:prefer-const
    let regx = /^[0-9]{9}[x|X|v|V]|[0-9]{12}$/;
    return nic.match(regx);
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
    this.formattedTodayValue = this.getFormatedToday();

  }

  changeProgressStatuses(newStatus = 0) {
    this.stepOn = newStatus;

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
          if ( req['data']['companytypeValid'] === false ) {

            this.spinner.hide();
            alert('You do not need to file form 09');
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          this.moduleStatus = req['data']['moduleStatus'];

          if (this.moduleStatus === 'COMPANY_SHARE_FORM9_RESUBMIT' ) {
            this. document_confirm = true;
          }

          if ( !( this.moduleStatus === 'COMPANY_SHARE_FORM9_PROCESSING' || this.moduleStatus === 'COMPANY_SHARE_FORM9_RESUBMIT' ) ) {
            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          // tslint:disable-next-line:radix
          this.requestId = req['data']['request_id'] ? parseInt( req['data']['request_id'] ) : 0;

          this.compayType = req['data']['companyType'];
          this.companyRegNumber = req['data']['certificate_no'];
          this.processStatus = req['data']['processStatus'];
          this.postFixEn = req['data']['postfix'];
          this.postFixSi = req['data']['postfix_si'];
          this.postFixTa = req['data']['postfix_ta'];
          if (req['data']['signedby'] && req['data']['signedbytype']) {
            if (req['data']['signedbytype'] === 'COMPANY_MEMBERS') {
              this.convert = req['data']['signedby'];
              this.signbyid = this.convert.toString() + '-' + 0;

            }
            else if (req['data']['signedbytype'] === 'COMPANY_MEMBER_FIRMS') {
              this.convert = req['data']['signedby'];
              this.signbyid = this.convert.toString() + '-' + 1;

            }

          }


          this.companyInfo = req['data']['companyInfo'];
          this.loginUserInfo = req['data']['loginUser'];
          this.loginUserAddress = req['data']['loginUserAddress'];
          this.members = req['data']['members'];
          this.nicarray = req['data']['nicarray'];
          this.passarray = req['data']['passportarray'];
          this.regnoarray = req['data']['regnoarray'];


         // this.callShareList.share = req['data']['share_calls'];
         // this.shareholdersList.sh = req['data']['shareholders'];
         // this.shareholderFirmList.sh = req['data']['shareholder_firms'];
         this.form9RecordList.rec = req['data']['share_calls'];
         this.share_types = req['data']['share_types'];
          this.docList = req['data']['downloadDocs'];
          this.uploadList = req['data']['uploadDocs'];
          this.uploadOtherList = req['data']['uploadOtherDocs'];
          this.uploadOther64or67List = req['data']['uploadOther64or67Docs'];
          this.uploadOther31Docs = req['data']['uploadOther31Docs'];
          this.uploadOtherCoDocs = req['data']['uploadOtherCoDocs'];
          this.allFilesUploaded = this.uploadList['uploadedAll'];

          this.externalGlobComment = req['data']['external_global_comment'];

        //  this.stated_capital = req['data']['callonSharesRecord']['stated_capital'];
         // this.signing_party_designation =  req['data']['callonSharesRecord']['signing_party_designation'];
         // this.singning_party_name =  req['data']['callonSharesRecord']['signing_party_name'];
          this.total_company_shares =  req['data']['callonSharesRecord']['total_company_shares'];
          this.type_of_64_67 =  req['data']['callonSharesRecord']['type_of_64_67'];
          this.ground =  JSON.parse(req['data']['callonSharesRecord']['ground']);


          this.form7_payment = (req['data']['form7_payment']) ? parseFloat( req['data']['form7_payment'] ) : 0;
          this.vat = (req['data']['vat']) ? parseFloat( req['data']['vat'] ) : 0;
          this.other_tax = (req['data']['other_tax']) ? parseFloat( req['data']['other_tax'] ) : 0;
          this.convinienceFee = (req['data']['convinienceFee']) ? parseFloat( req['data']['convinienceFee'] ) : 0;

          this.total_wihtout_vat_tax = this.form7_payment;

          this.other_taxVal = ( this.total_wihtout_vat_tax * this.other_tax ) / 100;
          this.vatVal = ( this.total_wihtout_vat_tax + this.other_taxVal ) * this.vat / 100;
          this.convinienceFeeVal = ( this.total_wihtout_vat_tax + this.other_taxVal  + this.vatVal ) * this.convinienceFee / 100;
          this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;

          this.changeProgressStatuses(this.stepOn);
          this.checkGroundType();

          this.spinner.hide();
        }
      );



  }

  checkGroundType(){
    console.log('11');
    if (this.ground) {
      if (this.ground.length) {
        console.log('22');
        for ( var i = 0; i < this.ground.length; i++) {
          if ( this.ground[i] === '64' || this.ground[i] === '67') {
            this.groundType = true;
            console.log('33');
            return true;
          }
       }
       this.type_of_64_67 = null;
       this.groundType = false;
       console.log('44');
       return false;
      }
      this.type_of_64_67 = null;
      this.groundType = false;
      console.log('55');
      return false;

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

  uploadOtherDoc(event, fileNane, fileDBID,  other_doc_name ) {


    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {

     for (let i = 0; i < fileList.length; i++ ) {

      // tslint:disable-next-line:prefer-const
      let file: File = fileList[i];

     // console.log(fileList[0]);

      // tslint:disable-next-line:prefer-const
      let fileSize = fileList[i].size;

      if (fileSize >= 1024 * 1024 * 4) { // 4mb restriction
        alert('You can upload document only up to 4 MB');
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile', file, file.name);
      formData.append('fileName', this.slugify(fileNane));
      let filename = (other_doc_name) ?  other_doc_name + '.pdf' : file.name;
      formData.append('fileRealName', filename );
      formData.append('fileDescription', other_doc_name);
      formData.append('fileTypeId', fileDBID);
      formData.append('company_id', this.companyId );
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
            this.loadData();
            this.other_doc_name = '';
            this.other_doc64or67_name = '';
            this.other_doc31_name = '';
            this.other_docCO_name = '';
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


  uploadOtherResumittedDoc(event, multiple_id  ) {


    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {

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
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.other_doc_name = '';
            this.other_doc64or67_name = '';
            this.other_doc31_name = '';
            this.other_docCO_name = '';
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
  for (let i in this.form9RecordList.rec) {
    if (this.form9RecordList.rec[i]['id'] === recId) {
      this.form9RecordList.rec[i]['showEditPane'] = this.form9RecordList.rec[i]['showEditPane'] === recId ? null : recId;
      return true;
    }
  }
}

resetCallRecord() {
  this.rec = {id: null, nic: null, other_share_class: null, regno: null, showEditPane: 0, shareholder_id: '', person_name: '' , norm_type : 'Person', aquire_or_redeemed : 'acquire', date: '', share_class: '', aquire_or_redeemed_value: ''};
}



validateCallRecordEdit(i) {
  console.log(i);
  let row = this.form9RecordList.rec[i];
  if (!
    (
      row.person_name &&
      row.aquire_or_redeemed &&
      row.date &&
      row.share_class && (row.share_class === 'OTHER_SHARE' ? row.other_share_class : true) &&
      row.aquire_or_redeemed_value && parseFloat(row.aquire_or_redeemed_value) &&
      row.norm_type && (row.norm_type === 'Person' ? (row.person_type === 'NIC' ? row.nic : row.passno) : (row.norm_type === 'Corporate-body' ? row.regno : false)) &&
      this.includenic(row.nic) &&
      this.includepass(row.passno) &&
      this.includeregno(row.regno) &&
      this.validateNIC(row.nic)
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

makeNull1Edit(i){
  let row = this.form9RecordList.rec[i];
  if (row.person_type === 'NIC') {
    row.passno = null;

  }
  else if (row.person_type === 'Passport-No') {
    row.nic = null;
  }
}

makeNull1(){
  if (this.rec.person_type === 'NIC') {
    this.rec.passno = null;

  }
  else if (this.rec.person_type === 'Passport-No') {
    this.rec.nic = null;
  }
}

makeNullEdit(i){
  let row = this.form9RecordList.rec[i];
  if (row.norm_type === 'Person') {
    row.regno = null;

  }
  else if (row.norm_type === 'Corporate-body') {
    row.nic = null;
    row.person_type = null;
  }
}

makeNull(){
  if (this.rec.norm_type === 'Person') {
    this.rec.regno = null;

  }
  else if (this.rec.norm_type === 'Corporate-body') {
    this.rec.nic = null;
    this.rec.person_type = null;
  }
}

makeNullOther(){
  if (!(this.rec.share_class === 'OTHER_SHARE')) {
    this.rec.other_share_class = null;

  }
}

makeNullOtherEdit(i){
  let row = this.form9RecordList.rec[i];
  if (!(row.share_class === 'OTHER_SHARE')) {
    row.other_share_class = null;

  }
}

private includenic(nic) {
  if (!nic) {
    return true;
  }
  // tslint:disable-next-line:prefer-const
  // let regx = /^[0-9]{9}[x|X|v|V]|[0-9]{12}$/;
  return this.nicarray.includes(nic.toUpperCase());
}

private includepass(pass) {
  if (!pass) {
    return true;
  }
  // tslint:disable-next-line:prefer-const
  // let regx = /^[0-9]{9}[x|X|v|V]|[0-9]{12}$/;
  return this.passarray.includes(pass);
}

private includeregno(regno) {
  if (!regno) {
    return true;
  }
  // tslint:disable-next-line:prefer-const
  // let regx = /^[0-9]{9}[x|X|v|V]|[0-9]{12}$/;
  return this.regnoarray.includes(regno.toUpperCase());
}


validateCallRecord() {
  console.log(this.ground);
  if (!
    (
      // tslint:disable-next-line:radix
      this.rec.person_name &&
      this.rec.aquire_or_redeemed &&
      this.rec.date &&
      this.rec.share_class && (this.rec.share_class === 'OTHER_SHARE' ? this.rec.other_share_class : true) &&
      this.rec.aquire_or_redeemed_value && parseFloat(this.rec.aquire_or_redeemed_value) &&
      this.rec.norm_type && (this.rec.norm_type === 'Person' ? (this.rec.person_type === 'NIC' ? this.rec.nic : this.rec.passno) : (this.rec.norm_type === 'Corporate-body' ? this.rec.regno : false)) &&
      this.includenic(this.rec.nic) &&
      this.includepass(this.rec.passno) &&
      this.includeregno(this.rec.regno) &&
      this.validateNIC(this.rec.nic)
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
  let copy = Object.assign({}, this.rec);
  this.form9RecordList.rec.push(copy);

  // tslint:disable-next-line:max-line-length
  this.rec = {id: null, nic: null, regno: null, other_share_class: null, showEditPane: 0, shareholder_id: '', person_name: '' , norm_type : 'Person', aquire_or_redeemed : 'acquire', date: '', share_class: '', aquire_or_redeemed_value: ''};
  this.validShareRecordItem = false;
  this.submitShareRecord('remove');

}

submitShareRecord(action = '') {

  const data = {
    companyId: this.companyId,
    loginUser: this.loginUserEmail,
    call_records: this.form9RecordList,
    // signing_party_designation: this.signing_party_designation,
    // singning_party_name: this.singning_party_name,
    ground: this.ground,
    type_of_64_67: this.type_of_64_67,
    total_company_shares: this.total_company_shares,
    signby: this.signbyid,
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

  this.form9RecordList.rec.splice(i, 1);
  if (!recId) {
    return true;
  }
  this.submitShareRecord('remove');

}


/**************End SHARE Functions*******************/


  pay() {


    this.paymentItems.push(
      {
          fee_type: 'PAYMENT_FORM9',
          description: 'ACQUISITION OR REDEMPTION BY COMPANY Payment',
          quantity: 1,
      }

    );

    const buy: IBuy = {
      module_type: 'MODULE_COMPANY_SHARE_FORM9',
      module_id: this.requestId.toString(),
      description: 'ACQUISITION OR REDEMPTION BY COMPANY',
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

}



