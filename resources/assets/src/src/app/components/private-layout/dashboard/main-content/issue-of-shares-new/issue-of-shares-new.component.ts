import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { APIform6Connection } from './services/connections/APIform6Connection';
import { count, distinct } from 'rxjs/operators';
import { GeneralService } from '../../../../../http/services/general.service';
import { HelperService } from '../../../../../http/shared/helper.service';
import { DataService } from '../../../../../storage/data.service';
import { PaymentService } from '../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../http/models/payment';
import { isNull } from '@angular/compiler/src/output/output_ast';
import { Form6Service } from './services/form6.service';
import { environment } from '../../../../../../environments/environment';
import { IDownloadDocs, IUploadDocs, IshareholderItems, ISignedStakeholder, IissueShares, IissueShare} from './models/form6.model';
import { IcompanyType, IcompanyInfo, IloginUser, IloginUserAddress, Icountry, IcoreShareGroup } from '../../../../../http/models/incorporation.model';
import { IShareHolders, IShareHolder, IShareHolderBenifList, IShareHolderBenif, IProvince, IDistrict, ICity } from '../../../../../http/models/stakeholder.model';
import { interval } from 'rxjs';
import { IbulkShareholderBulkInfo } from '../annual-return/models/annualReturn.model';
@Component({
  selector: 'app-issue-of-shares-new',
  templateUrl: './issue-of-shares-new.component.html',
  styleUrls: ['./issue-of-shares-new.component.scss']
})
export class IssueOfSharesNewComponent implements OnInit, AfterViewInit {

  url: APIform6Connection = new APIform6Connection();

cipher_message: string;
paymentItems: Array<Item> = [];
paymentGateway: string = environment.paymentGateway;


signedDirectors: Array<ISignedStakeholder> = [];
signedSecs: Array<ISignedStakeholder> = [];
signedSecFirms: Array<ISignedStakeholder> = [];
shareTypes =  [];
isShareholderLable = 'Shareholder';

formattedTodayValue = '';

payConfirm = false;
form7_payment = 0;
vat = 0;
vatVal = 0;

stated_capital = '';

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

showCSVwrapper = false;
csvUploadMessage = '';

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
      { label: 'Issued Shares', icon: 'fas fa-share', status: '' },
      { label: 'Shareholders', icon: 'fas fa-users', status: '' },
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
  loadNICstakeholders = false;
  openAddressPart = false;
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


  callShareList: IissueShares = {share: [] };
  public call: IissueShare = {id: null, showEditPane: 0, selected_share_class_name: '', share_class: '', no_of_shares: '', share_class_other: '', date_of_issue: '' , is_issue_type_as_cash: '', no_of_shares_as_cash: '', consideration_of_shares_as_cash: '', is_issue_type_as_non_cash: '', no_of_shares_as_non_cash: '', consideration_of_shares_as_non_cash: ''};


  shList: IShareHolders = { shs: [] };
  shFirmList: IShareHolders = { shs: [] };
  // tslint:disable-next-line:max-line-length
  public sh: IShareHolder = { id: 0, showEditPaneForSh: 0, shareIssueClass: '', type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, noOfSharesGroup: null, groupAddedValue: '0', shareholderType: 'natural', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , firm_city: '', firm_district: '', firm_province: '',  benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null }, passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: '', status: null };
  benifList: IShareHolderBenifList = { ben: [] };
  public sh_benif: IShareHolderBenif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] };
  coreShareGroups: Array<IcoreShareGroup> = [];
  countries: Array<Icountry> = [];
  provinces: Array<IProvince> = [];
  districts: Array<IDistrict> = [];
  cities: Array<ICity> = [];
  shareRecordsList = [];

  share_records_already_exists = true;

  signing_party_designation = '';
  singning_party_name = '';

  bulkShareholderInfo: IbulkShareholderBulkInfo = {  screen1Provinces: [], screen1Districts: [], screen1Cities: [], province: '', district: '', city: '', country: 'Sri Lanka', title: ''};
  shareholder_bulk_format = '';
  example_member_bulk_data = '';

  isShareIssuedProperlyStatus = false;
  issuedRecords = [];

  constructor(
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private callShareService: Form6Service,
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

    this.progress.progressPercentage = (this.stepOn >= 2) ? (8.33 * 2 + this.stepOn * 16.67) + '%' : (8.33 + this.stepOn * 16.67) + '%';

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

          if ( !( this.moduleStatus === 'COMPANY_ISSUE_OF_SHARES_PROCESSING' || this.moduleStatus === 'COMPANY_ISSUE_OF_SHARES_REQUEST_TO_RESUBMIT' ) ) {
            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          this.formattedTodayValue = this.getFormatedToday();

          // tslint:disable-next-line:radix
          this.requestId = req['data']['request_id'] ? parseInt( req['data']['request_id'] ) : 0;

          this.compayType = req['data']['companyType'];


          this.isGuarantyCompany =  ( this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34' );

          if (this.isGuarantyCompany) {
            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          this.processStatus = req['data']['processStatus'];
          this.postFixEn = req['data']['postfix'];
          this.postFixSi = req['data']['postfix_si'];
          this.postFixTa = req['data']['postfix_ta'];
          this.companyRegNumber = req['data']['certificate_no'];

          this.provinces = req['data']['cpd']['provinces'];
          this.districts = req['data']['cpd']['districts'];
          this.cities = req['data']['cpd']['cities'];
          this.countries = req['data']['countries'];

          this.shareholder_bulk_format = req['data']['shareholder_bulk_format'];
          this.example_member_bulk_data = req['data']['example_member_bulk_data'];


          this.companyInfo = req['data']['companyInfo'];
          this.loginUserInfo = req['data']['loginUser'];
          this.loginUserAddress = req['data']['loginUserAddress'];

          this.signedDirectors = req['data']['directors'];
          this.signedSecs = req['data']['secs'];
          this.signedSecFirms = req['data']['sec_firms'];
          this.shareTypes = req['data']['shareTypes'];


          this.callShareList.share = req['data']['share_calls'];

          this.stated_capital = req['data']['callonSharesRecord']['stated_capital'];
          this.shareholdersList.sh = req['data']['shareholders'];
          this.shareholderFirmList.sh = req['data']['shareholder_firms'];

          this.shList.shs = req['data']['shareholdersList'];
          if (this.shList.shs.length) {
            // tslint:disable-next-line:prefer-const
            for (let i in this.shList.shs) {
              console.log( this.shList.shs[i] );
              // tslint:disable-next-line:prefer-const
              let shareRow = this.shList.shs[i].shareRow;

              if (this.shList.shs[i].shareType === 'single') {
                this.shList.shs[i].noOfShares = shareRow.no_of_shares;
              }
              if (this.shList.shs[i].shareType === 'core') {
                this.shList.shs[i].coreGroupSelected = shareRow.sharegroupId;
              }


            }
          }

          this.shFirmList.shs = req['data']['shareholderFirmList'];
          if (this.shFirmList.shs.length) {
            // tslint:disable-next-line:prefer-const
            for (let i in this.shFirmList.shs) {
              // tslint:disable-next-line:prefer-const
              let shareRow = this.shFirmList.shs[i].shareRow;

              if (this.shFirmList.shs[i].shareType === 'single') {
                this.shFirmList.shs[i].noOfShares = shareRow.no_of_shares;
              }
              if (this.shFirmList.shs[i].shareType === 'core') {
                this.shFirmList.shs[i].coreGroupSelected = shareRow.sharegroupId;
              }
              this.shFirmList.shs[i].benifOwnerType = 'local';

              this.getProvincesForStakeHolderEdit('shFirm', i);
              this.getDistrictsForStakeholderEdit('shFirm', i , this.shFirmList.shs[i].firm_province, true );
              this.getCitiesForStakeholderEdit( 'shFirm', i , this.shFirmList.shs[i].firm_district, true  );

              this.getProvincesForBenEdit('sh_benif', i );
              this.getDistrictsForBenEdit ('sh_benif', i , this.sh_benif.province );
              this.getCitiesForBenEdit( 'sh_benif', i , this.sh_benif.district );

            }
          }


          // tslint:disable-next-line:prefer-const
          for (let i in this.shList.shs) {
            this.getProvincesForStakeHolderEdit('sh', i);
            this.getDistrictsForStakeholderEdit('sh', i , this.shList.shs[i].province, true );
            this.getCitiesForStakeholderEdit( 'sh', i , this.shList.shs[i].district, true  );
           // this.validateShareHolderEdit(i);
          }
          this.coreShareGroups = req['data']['coreShareGroups'];
          this.shareRecordsList = req['data']['shareRecordsList'];

          this.isShareIssuedProperlyStatus = req['data']['isShareIssuedProperly']['status'];
          this.issuedRecords = req['data']['isShareIssuedProperly']['share_arr'];

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


          this.bulkShareholderInfo = {  screen1Provinces: [], screen1Districts: [], screen1Cities: [], province: '', district: '', city: '', country: 'Sri Lanka', title: ''};
          this.getProvincesShareholderInfo();

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

          this.enableStep2Submission = true;

          this.spinner.hide();
        }
      );



  }

  getProvincesShareholderInfo() {

    // tslint:disable-next-line:prefer-const
    let provinces = Object.assign({}, this.provinces);
    // tslint:disable-next-line:prefer-const
    let filterProvince: Array<IProvince> = [];
   // tslint:disable-next-line:prefer-const
   for ( let i in provinces ) {
    filterProvince.push( provinces[i]);
    }
    this.bulkShareholderInfo.screen1Provinces = filterProvince;
  }

  getDisctrictShareholderInfo(provinceName, load = false ) {

    this.bulkShareholderInfo.screen1Cities = [];
    this.bulkShareholderInfo.screen1Districts = [];

   // tslint:disable-next-line:prefer-const
   let districts = Object.assign({}, this.districts);
   // tslint:disable-next-line:prefer-const
   let filterDistricts: Array<IDistrict> = [];

   // tslint:disable-next-line:prefer-const
   for ( let i in districts ) {
       if ( districts[i].provinceName === provinceName ) {

        filterDistricts.push( districts[i]);
       }
   }

    this.bulkShareholderInfo.screen1Districts = filterDistricts;
  }

  getCityShareholderInfo(districtName, load = false ) {

    this.bulkShareholderInfo.screen1Cities = [];

    // tslint:disable-next-line:prefer-const
    let cities = Object.assign({}, this.cities);
    // tslint:disable-next-line:prefer-const
    let filterCities: Array<ICity> = [];
    // tslint:disable-next-line:prefer-const
    for ( let i in cities ) {
        if ( cities[i].districtName === districtName ) {
          filterCities.push( cities[i]);
        }
    }
     this.bulkShareholderInfo.screen1Cities = filterCities;
   }

  changeDefaultStatus() {


    // tslint:disable-next-line:max-line-length
    this.sh = { id: 0, groupAddedValue: '0', shareIssueClass: '', showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , firm_city: '', firm_district: '', firm_province: '',  benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '' , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: '', status: null };
    this.sh_benif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] };

    this.sh.type = 'local';
    this.loadNICstakeholders = false;
    this.directorNicLoaded = false;
    this.openAddressPart = false;
    this.shNicLoaded = false;

    this.getProvincesForStakeHolder('sh');

    this.getProvincesForBen('sh_benif');



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


  shareholderBulkUpload(event ) {

    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      // tslint:disable-next-line:prefer-const
      let file: File = fileList[0];

      console.log(fileList[0]);

      // tslint:disable-next-line:prefer-const
      let fileSize = fileList[0].size;

     // this.resetUploadElem();

      if (fileSize > 1024 * 1024 * 4) { // 4mb restriction
        alert('File size should be less than 4 MB');
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile', file, file.name);
      formData.append('fileRealName', file.name );
      formData.append('companyId', this.companyId );

      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.shareholderCSV();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            if ( data['status']) {
              this.loadData();
              this.changeProgressStatuses(2);
              this.csvUploadMessage = data['message'];
              this.showCSVwrapper = false;

            } else {
              this.spinner.hide();
              this.changeProgressStatuses(2);
              alert(data['message']);

            }

          },
          error => {
            alert(error);
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
  this.call = {id: null, showEditPane: 0, selected_share_class_name: '', share_class: '', no_of_shares: '',  share_class_other: '', date_of_issue: '' , is_issue_type_as_cash: '', no_of_shares_as_cash: '', consideration_of_shares_as_cash: '', is_issue_type_as_non_cash: '', no_of_shares_as_non_cash: '', consideration_of_shares_as_non_cash: ''};
  this.validateCallRecord();
}



validateCallRecordEdit(i) {

  let row = this.callShareList.share[i];


  if (
    row.share_class &&
    ( row.share_class === 'OTHER_SHARE' ? row.share_class_other : true ) &&
    row.date_of_issue &&
    ( row.is_issue_type_as_cash && row.is_issue_type_as_non_cash ) &&
    ( row.is_issue_type_as_cash === 'yes' || row.is_issue_type_as_non_cash === 'yes') &&
    ( row.is_issue_type_as_cash === 'yes' ? row.no_of_shares_as_cash && parseFloat(row.no_of_shares_as_cash) && row.consideration_of_shares_as_cash && parseFloat(row.consideration_of_shares_as_cash) : true ) &&
    ( row.is_issue_type_as_non_cash === 'yes' ? row.no_of_shares_as_non_cash && parseFloat(row.no_of_shares_as_non_cash) && row.consideration_of_shares_as_non_cash : true )

  ) {
    this.shareRecordValitionMessage = '';
    this.enableStep2Submission = true;
    this.enableStep2SubmissionEdit = true;
    return true;
  } else {
    this.shareRecordValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
    this.enableStep2Submission = false;
    this.enableStep2SubmissionEdit = false;
    return false;
  }
}


validateCallRecord() {

  if (

    this.call.share_class &&
    ( this.call.share_class === 'OTHER_SHARE' ? this.call.share_class_other : true ) &&
    this.call.date_of_issue &&
    ( this.call.is_issue_type_as_cash && this.call.is_issue_type_as_non_cash ) &&
    ( this.call.is_issue_type_as_cash === 'yes' || this.call.is_issue_type_as_non_cash === 'yes') &&
    ( this.call.is_issue_type_as_cash === 'yes' ? this.call.no_of_shares_as_cash && parseFloat(this.call.no_of_shares_as_cash) && this.call.consideration_of_shares_as_cash && parseFloat(this.call.consideration_of_shares_as_cash) : true ) &&
    ( this.call.is_issue_type_as_non_cash === 'yes' ? this.call.no_of_shares_as_non_cash && parseFloat(this.call.no_of_shares_as_non_cash) && this.call.consideration_of_shares_as_non_cash  : true )
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


 saveShareRecord() {

  // tslint:disable-next-line:prefer-const
  let copy = Object.assign({}, this.call);
  this.callShareList.share.push(copy);

  // tslint:disable-next-line:max-line-length
  this.call = {id: null, showEditPane: 0, selected_share_class_name: '', share_class: '', no_of_shares: '',  share_class_other: '', date_of_issue: '' , is_issue_type_as_cash: '', no_of_shares_as_cash: '', consideration_of_shares_as_cash: '', is_issue_type_as_non_cash: '', no_of_shares_as_non_cash: '', consideration_of_shares_as_non_cash: ''};
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

  this.spinner.show();

  this.callShareService.shareCallSubmit(data)
    .subscribe(
      req => {
        this.loadData();
        if (action === 'remove') {
          this.changeProgressStatuses(1);
          this.spinner.hide();
          return false;
        }
        this.spinner.hide();
        this.changeProgressStatuses(2);
      },
      error => {
        this.spinner.hide();
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
          fee_type: 'PAYMENT_COMPANY_ISSUE_OF_SHARES',
          description: 'For Company Issue of share (Change Request)',
          quantity: 1,
      }

    );

    const buy: IBuy = {
      module_type: 'MODULE_COMPANY_ISSUE_OF_SHARES',
      module_id: this.requestId.toString(),
      description: 'Company Issue of shares',
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
            this.changeProgressStatuses(5);

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

private validateEmail(email) {
  if (!email) { return false; }
  // tslint:disable-next-line:prefer-const
 // let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
   // tslint:disable-next-line:prefer-const
// let re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

 // tslint:disable-next-line:prefer-const
 let re = /^[A-Za-z0-9]([a-zA-Z0-9]+([_.-][a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,3})$/;
 // return re.test(String(email).toLowerCase());
  return re.test(email);
}
private phonenumber(inputtxt, type = 'local') {
  if (!inputtxt) { return true; }
  // tslint:disable-next-line:prefer-const
  let phoneno = type === 'foreign' ? /^\d{10,15}$/   : /^\d{10}$/;
  return inputtxt.match(phoneno);
}
private validateNIC(nic) {
  if (!nic) {
    return false;
  }
  // tslint:disable-next-line:prefer-const
  let regx = /^[0-9]{9}[x|X|v|V]|[0-9]{12}$/;
  return nic.match(regx);
}


validateShareHolder() {

  if (this.sh.type === 'local' && this.sh.shareholderType === 'natural') {

    if (!(this.sh.nic && this.validateNIC(this.sh.nic) &&
    ( this.isGuarantyCompany ? true : ( this.sh.shareType && (this.sh.coreGroupSelected || this.sh.noOfShares || (this.sh.coreShareGroupName && this.sh.noOfSharesGroup) ) ) )
      &&
      this.sh.title &&
      this.sh.email && this.validateEmail(this.sh.email) &&
      this.sh.firstname &&
      this.sh.lastname &&
      this.sh.province &&
      this.sh.district &&
      this.sh.city &&
      this.sh.localAddress1 &&
      this.sh.postcode &&
      this.sh.date &&
      this.sh.mobile && this.phonenumber(this.sh.mobile, this.sh.type) &&
      this.sh.shareIssueClass
    )) {

      this.shValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validSh = false;
      return false;
    } else {
      this.shValitionMessage = '';
      this.validSh = true;
      return true;

    }

  }
  if (this.sh.type === 'local' && this.sh.shareholderType === 'firm') {

    if (!(this.sh.firm_name &&
      this.sh.firm_city &&
      this.sh.firm_district &&
      this.sh.firm_province &&
      this.sh.firm_localAddress1 &&
      this.sh.firm_postcode &&
      this.sh.firm_date &&
      this.sh.firm_email && this.validateEmail(this.sh.firm_email) &&
      this.sh.firm_mobile && this.phonenumber(this.sh.firm_mobile, this.sh.type) &&
      ( this.isGuarantyCompany ? true : ( this.sh.shareType && (this.sh.coreGroupSelected || this.sh.noOfShares || (this.sh.coreShareGroupName && this.sh.noOfSharesGroup) ) ) ) &&
      // && this.benifList.ben.length
      this.sh.shareIssueClass

    )) {

      this.shValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validSh = false;
      return false;
    } else {
      this.shValitionMessage = '';
      this.validSh = true;
      return true;

    }

  }

  if (this.sh.type === 'foreign' && this.sh.shareholderType === 'natural' ) {
    if (!(this.sh.passport && !this.isShAlreadyExist('foreign') && this.sh.passport_issued_country &&
      this.sh.title &&
      this.sh.email && this.validateEmail(this.sh.email) &&
      this.sh.firstname &&
      this.sh.lastname &&
      this.sh.country &&
      this.sh.forCity &&
      this.sh.forProvince &&
      this.sh.forAddress1 &&
      this.sh.forPostcode &&
      this.sh.date &&
      this.sh.mobile && this.phonenumber(this.sh.mobile, this.sh.type) &&
      ( this.isGuarantyCompany ? true : ( this.sh.shareType && (this.sh.coreGroupSelected || this.sh.noOfShares || (this.sh.coreShareGroupName && this.sh.noOfSharesGroup) ) ) ) &&
      this.sh.shareIssueClass
    )) {

      this.shValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validSh = false;
      return false;

    } else {
      this.shValitionMessage = '';
      this.validSh = true;
      return true;

    }

  }

  if (this.sh.type === 'foreign' && this.sh.shareholderType === 'firm') {

    if (!(this.sh.firm_name &&
      this.sh.firm_city &&
      this.sh.country &&
      this.sh.firm_province &&
      this.sh.firm_localAddress1 &&
      this.sh.firm_postcode &&
      this.sh.firm_date &&
      this.sh.firm_email && this.validateEmail(this.sh.firm_email) &&
      this.sh.firm_mobile && this.phonenumber(this.sh.firm_mobile, this.sh.type) &&
      ( this.isGuarantyCompany ? true : ( this.sh.shareType && (this.sh.coreGroupSelected || this.sh.noOfShares || (this.sh.coreShareGroupName && this.sh.noOfSharesGroup) ) ) ) &&
      // && this.benifList.ben.length
      this.sh.shareIssueClass

    )) {

      this.shValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validSh = false;
      return false;
    } else {
      this.shValitionMessage = '';
      this.validSh = true;
      return true;

    }

  }

}
validateShareHolderEdit(rowId, isFirm = false) {

  // tslint:disable-next-line:prefer-const
  let shRow = (isFirm) ? this.shFirmList.shs[rowId] :  this.shList.shs[rowId];

  if (shRow.type === 'local' && shRow.shareholderType === 'natural') {

    if (!(shRow.nic && this.validateNIC(shRow.nic) &&
      shRow.title &&
      shRow.email && this.validateEmail(shRow.email) &&
      shRow.firstname &&
      shRow.lastname &&
      shRow.province &&
      shRow.district &&
      shRow.city &&
      shRow.localAddress1 &&
      shRow.postcode &&
      shRow.date &&
      shRow.mobile && this.phonenumber(shRow.mobile, shRow.type) &&
      ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares || (shRow.coreShareGroupName && shRow.noOfSharesGroup)) ) &&
      // tslint:disable-next-line:radix
      ( parseInt(shRow.status) === 1 && shRow.shareIssueClass  ? shRow.groupAddedValue && parseInt(shRow.groupAddedValue) : true)
    )) {

      this.enableStep2Submission = false;
      this.enableStep2SubmissionEdit = false;
      return false;
    } else {
      this.enableStep2Submission = true;
      this.enableStep2SubmissionEdit = true;
      return true;

    }

  }
  if (shRow.type === 'local' && shRow.shareholderType === 'firm') {

    if (!(shRow.firm_name &&
      shRow.firm_city &&
      shRow.firm_province &&
      shRow.firm_localAddress1 &&
      shRow.firm_postcode &&
      shRow.firm_date &&
      shRow.firm_email && this.validateEmail(shRow.firm_email) &&
      shRow.firm_mobile && this.phonenumber(shRow.firm_mobile, shRow.type ) &&
      ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares || (shRow.coreShareGroupName && shRow.noOfSharesGroup)) ) &&
      // tslint:disable-next-line:radix
      ( parseInt(shRow.status) === 1 && shRow.shareIssueClass  ? shRow.groupAddedValue && parseInt(shRow.groupAddedValue) : true)
     //  &&  shRow.benifiList.ben.length
    )) {

      this.enableStep2Submission = false;
      this.enableStep2SubmissionEdit = false;
      return false;
    } else {
      this.enableStep2Submission = true;
      this.enableStep2SubmissionEdit = true;
      return true;

    }

  }

  if (shRow.type === 'foreign'  && shRow.shareholderType === 'natural' ) {
    if (!(shRow.passport && shRow.passport_issued_country &&
      shRow.title &&
      shRow.email && this.validateEmail(shRow.email) &&
      shRow.firstname &&
      shRow.lastname &&
      shRow.country &&
      shRow.forCity &&
      shRow.forProvince &&
      shRow.forAddress1 &&
      shRow.forPostcode &&
      shRow.date &&
      shRow.mobile && this.phonenumber(shRow.mobile, shRow.type ) &&
     //  shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares)
     ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares || (shRow.coreShareGroupName && shRow.noOfSharesGroup)) ) &&
      // tslint:disable-next-line:radix
      ( parseInt(shRow.status) === 1 && shRow.shareIssueClass  ? shRow.groupAddedValue && parseInt(shRow.groupAddedValue) : true)
    )) {

      this.enableStep2Submission = false;
      this.enableStep2SubmissionEdit = false;
      return true;

    } else {
      this.enableStep2Submission = true;
      this.enableStep2SubmissionEdit = true;
      return true;

    }

  }

  if (shRow.type === 'foreign'  && shRow.shareholderType === 'firm' ) {
    if (!(shRow.firm_name &&
      shRow.firm_city &&
      shRow.country &&
      shRow.firm_province &&
      shRow.firm_localAddress1 &&
      shRow.firm_postcode &&
      shRow.firm_date &&
      shRow.firm_email && this.validateEmail(shRow.firm_email) &&
      shRow.firm_mobile && this.phonenumber(shRow.firm_mobile, shRow.type ) &&
     //  shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares)
     ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares || (shRow.coreShareGroupName && shRow.noOfSharesGroup)) ) &&
      // tslint:disable-next-line:radix
      ( parseInt(shRow.status) === 1 && shRow.shareIssueClass  ? shRow.groupAddedValue && parseInt(shRow.groupAddedValue) : true)
     // && shRow.benifiList.ben.length
    )) {

      this.enableStep2Submission = false;
      this.enableStep2SubmissionEdit = false;
      return true;

    } else {
      this.enableStep2Submission = true;
      this.enableStep2SubmissionEdit = true;
      return true;

    }

  }

}

validateShBenif() {
  if (this.sh.benifOwnerType === 'local') {
    if (!
      (
        this.sh_benif.nic && this.validateNIC(this.sh_benif.nic) &&
        this.sh_benif.title &&
        this.sh_benif.email && this.validateEmail(this.sh_benif.email) &&
        this.sh_benif.firstname &&
        this.sh_benif.lastname &&
        this.sh_benif.province &&
        this.sh_benif.district &&
        this.sh_benif.city &&
        this.sh_benif.mobile && this.phonenumber(this.sh_benif.mobile, this.sh.benifOwnerType) &&
        this.sh_benif.localAddress1 &&
        this.sh_benif.date &&
        this.sh_benif.postcode
      )

    ) {
      this.validateShBenifFlag = false;
      return false;
    } else {
      this.validateShBenifFlag = true;
      return true;

    }

  }

  if (this.sh.benifOwnerType === 'foreign') {

    if (!(this.sh_benif.passport &&
      this.sh_benif.title &&
      this.sh_benif.email && this.validateEmail(this.sh_benif.email) &&
      this.sh_benif.firstname &&
      this.sh_benif.lastname &&
      this.sh_benif.province &&

      this.sh_benif.city &&
      this.sh_benif.country &&
      this.sh_benif.mobile && this.phonenumber(this.sh_benif.mobile, this.sh.benifOwnerType) &&
      this.sh_benif.localAddress1 &&
      this.sh_benif.date &&
      this.sh_benif.postcode
    )) {

      this.validateShBenifFlag = false;
      return false;

    } else {
      this.validateShBenifFlag = true;
      return true;

    }

  }
}

validateShBenifEdit(i) {
  if (this.shFirmList.shs[i].benifOwnerType === 'local') {
    if (!
      (
        this.sh_benif.nic && this.validateNIC(this.sh_benif.nic) &&
        this.sh_benif.title &&
        this.sh_benif.email && this.validateEmail(this.sh_benif.email) &&
        this.sh_benif.firstname &&
        this.sh_benif.lastname &&
        this.sh_benif.province &&
        this.sh_benif.district &&
        this.sh_benif.city &&
        this.sh_benif.mobile && this.phonenumber(this.sh_benif.mobile, this.shFirmList.shs[i].benifOwnerType ) &&
        this.sh_benif.localAddress1 &&
        this.sh_benif.date &&
        this.sh_benif.postcode

      )

    ) {
      this.shFirmList.shs[i].validateAddBenif = false;
      return false;
    } else {
      this.shFirmList.shs[i].validateAddBenif = true;
      return true;

    }

  }

  if (this.shFirmList.shs[i].benifOwnerType === 'foreign') {

    if (!(this.sh_benif.passport &&
      this.sh_benif.title &&
      this.sh_benif.email && this.validateEmail(this.sh_benif.email) &&
      this.sh_benif.firstname &&
      this.sh_benif.lastname &&
      this.sh_benif.province &&

      this.sh_benif.city &&
      this.sh_benif.country &&
      this.sh_benif.mobile && this.phonenumber(this.sh_benif.mobile, this.shFirmList.shs[i].benifOwnerType ) &&
      this.sh_benif.localAddress1 &&
      this.sh_benif.date &&
      this.sh_benif.postcode
    )) {

      this.shFirmList.shs[i].validateAddBenif = false;
      return false;

    } else {
      this.shFirmList.shs[i].validateAddBenif = true;
      return true;

    }

  }
}

shareholderNicList() {

  // tslint:disable-next-line:prefer-const
  let shs = this.shList.shs;
  // tslint:disable-next-line:prefer-const
  let shNICList = {
    'local': [],
    'foreign': []
  };

  if (!shs.length) {
    return shNICList;
  }

  // tslint:disable-next-line: prefer-const
  for (let i in shs) {

    if (shs[i].type === 'local') {
      shNICList.local.push(shs[i].nic.toLowerCase());
    }

    if (shs[i].type === 'foreign') {
      shNICList.foreign.push(shs[i].passport.toLowerCase());
    }

  }

  return shNICList;

}

isShAlreadyExist(shType = 'local') {

  const shList = this.shareholderNicList();

  const shLocalList = shList.local;
  const shForeignList = shList.foreign;

  if (shType === 'foreign') {
    return (shForeignList.indexOf(this.sh.passport.toLowerCase()) > -1);
  } else if (shType === 'local') {
    return (shLocalList.indexOf(this.sh.nic.toLowerCase()) > -1);
  } else {
    return false;
  }

}

showToggle(userType, userId = 0) {

  if (userType === 'shFirm') {
    // tslint:disable-next-line:prefer-const
    for (let i in this.shFirmList.shs) {

      if (this.shFirmList.shs[i]['id'] === userId) {

        this.shFirmList.shs[i]['showEditPaneForSh'] = this.shFirmList.shs[i]['showEditPaneForSh'] === userId ? null : userId;
        return true;
      }
    }
  }

  if (userType === 'sh') {

    // tslint:disable-next-line:prefer-const
    for (let i in this.shList.shs) {

      if (this.shList.shs[i]['id'] === userId) {
        this.shList.shs[i]['showEditPaneForSh'] = this.shList.shs[i]['showEditPaneForSh'] === userId ? null : userId;
        return true;
      }
    }
  }


}

removeShRecord(i: number, userId: number = 0) {

  if ( !confirm('Are you sure you want to remove this stakeholder?') ) {
    return true;
  }

  this.shList.shs.splice(i, 1);
  if (!userId) {
    return true;
  }
  this.submitShareholders('remove');

}

submitShareholders( action = '' ) {

  // tslint:disable-next-line:prefer-const
  let copyShList = Object.assign({}, this.shList);
  if (this.shFirmList.shs.length) {
    // tslint:disable-next-line:prefer-const
    for (let i in this.shFirmList.shs) {
      // tslint:disable-next-line:prefer-const
      let formRecord: IShareHolder = {
        id: this.shFirmList.shs[i].id,
        type: this.shFirmList.shs[i].type,
        title: '',
        firstname: '',
        lastname: '',
        province: '',
        district: '',
        city: '',
        phone: '',
        email: '',
        mobile: '',
        date: '',
        occupation: '',
        localAddress1: '',
        localAddress2: '',
        postcode: '',
        nic: '',
        passport: '', country: '', share: 0,
        pvNumber: this.shFirmList.shs[i].pvNumber,
        firm_name: this.shFirmList.shs[i].firm_name,
        firm_province: this.shFirmList.shs[i].firm_province,
        firm_district: this.shFirmList.shs[i].firm_district,
        firm_city: this.shFirmList.shs[i].firm_city,
        firm_localAddress1: this.shFirmList.shs[i].firm_localAddress1,
        firm_localAddress2: this.shFirmList.shs[i].firm_localAddress2,
        firm_postcode: this.shFirmList.shs[i].firm_postcode,
        firm_email: this.shFirmList.shs[i].firm_email,
        firm_phone: this.shFirmList.shs[i].firm_phone,
        firm_mobile: this.shFirmList.shs[i].firm_mobile,
        firm_date: this.shFirmList.shs[i].firm_date,
        shareholderType: this.shFirmList.shs[i].shareholderType,
        shareType: this.shFirmList.shs[i].shareType,
        noOfShares: this.shFirmList.shs[i].noOfShares,
        coreGroupSelected: this.shFirmList.shs[i].coreGroupSelected ? this.shFirmList.shs[i].coreGroupSelected : null,
        coreShareGroupName: this.shFirmList.shs[i].coreShareGroupName ? this.shFirmList.shs[i].coreShareGroupName : '',
        noOfSharesGroup:  this.shFirmList.shs[i].noOfSharesGroup ? this.shFirmList.shs[i].noOfSharesGroup : null,
        showEditPaneForSh: this.shFirmList.shs[i].showEditPaneForSh,
        benifiList: this.shFirmList.shs[i].benifiList
      };
      copyShList.shs.push(formRecord);
    }
   }

   const data = {
     companyId: this.companyId,
     loginUser: this.loginUserEmail,
     shareholders: copyShList,
     set_operation: 'active'
   };
   this.callShareService.annualShareholdersSubmit(data)
     .subscribe(
       req => {
         this.loadData();
         if (action === 'remove') { // in case of removing stakeholder, keep the position on same page.
           this.changeProgressStatuses(2);
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



getProvincesForStakeHolder(type) {

  // tslint:disable-next-line:prefer-const
  let provinces = Object.assign({}, this.provinces);
  // tslint:disable-next-line:prefer-const
  let filterProvince: Array<IProvince> = [];
 // tslint:disable-next-line:prefer-const
 for ( let i in provinces ) {
  filterProvince.push( provinces[i]);
  }

  if ( type === 'sh' ) {
      this.sh.screen1Provinces = filterProvince;
     this.validateShareHolder();
  }

}

getDistrictsForStakeholder(type, provinceName, load = false ) {

 // tslint:disable-next-line:prefer-const
 let districts = Object.assign({}, this.districts);
 // tslint:disable-next-line:prefer-const
 let filterDistricts: Array<IDistrict> = [];

 // tslint:disable-next-line:prefer-const
 for ( let i in districts ) {
     if ( districts[i].provinceName === provinceName ) {

      filterDistricts.push( districts[i]);
     }
 }
  if ( type === 'sh' ) {
     this.sh.screen1Districts = filterDistricts;
     if (load === false ) {
      this.sh.city = '';
      this.sh.district = '';
      this.sh.firm_city = '';
      this.sh.firm_district = '';
     }
    this.validateShareHolder();
  }

}

getCitiesForStakeholder(type, districtName, load = false ) {

  // tslint:disable-next-line:prefer-const
  let cities = Object.assign({}, this.cities);
  // tslint:disable-next-line:prefer-const
  let filterCities: Array<ICity> = [];
  // tslint:disable-next-line:prefer-const
  for ( let i in cities ) {
      if ( cities[i].districtName === districtName ) {
        filterCities.push( cities[i]);
      }
  }

    if ( type === 'sh' ) {
       this.sh.screen1Cities = filterCities;
       if (load === false ) {
        this.sh.city = '';
        this.sh.firm_city = '';
       }
      this.validateShareHolder();
    }
 }

 getProvincesForStakeHolderEdit(type, i) {

  // tslint:disable-next-line:prefer-const
  let provinces = Object.assign({}, this.provinces);
  // tslint:disable-next-line:prefer-const
  let filterProvince: Array<IProvince> = [];
 // tslint:disable-next-line:prefer-const
 for ( let j in provinces ) {
  filterProvince.push( provinces[j]);
  }

  if ( type === 'sh' ) {
      this.shList.shs[i].screen1Provinces = filterProvince;
     this.validateShareHolderEdit(i);
  }
  if ( type === 'shFirm' ) {
    this.shFirmList.shs[i].screen1Provinces = filterProvince;
   this.validateShareHolderEdit(i, true);
  }

}

getDistrictsForStakeholderEdit(type, i , provinceName, load = false ) {

 // tslint:disable-next-line:prefer-const
 let districts = Object.assign({}, this.districts);
 // tslint:disable-next-line:prefer-const
 let filterDistricts: Array<IDistrict> = [];

 // tslint:disable-next-line:prefer-const
 for ( let j in districts ) {
     if ( districts[j].provinceName === provinceName ) {

      filterDistricts.push( districts[j]);
     }
 }

  if ( type === 'sh' ) {
     this.shList.shs[i].screen1Districts = filterDistricts;
     if (load === false ) {
      this.shList.shs[i].city = '';
      this.shList.shs[i].district = '';
      this.shList.shs[i].firm_city = '';
      this.shList.shs[i].firm_district = '';
     }
    this.validateShareHolderEdit(i);
  }

  if ( type === 'shFirm' ) {
    this.shFirmList.shs[i].screen1Districts = filterDistricts;
    if (load === false ) {
     this.shFirmList.shs[i].firm_city = '';
     this.shFirmList.shs[i].firm_district = '';
    }
   this.validateShareHolderEdit(i, true);
 }

}

getCitiesForStakeholderEdit(type, i, districtName, load = false ) {

  // tslint:disable-next-line:prefer-const
  let cities = Object.assign({}, this.cities);
  // tslint:disable-next-line:prefer-const
  let filterCities: Array<ICity> = [];
  // tslint:disable-next-line:prefer-const
  for ( let j in cities ) {
      if ( cities[j].districtName === districtName ) {
        filterCities.push( cities[j]);
      }
  }

    if ( type === 'sh' ) {
       this.shList.shs[i].screen1Cities = filterCities;
       if (load === false ) {
        this.shList.shs[i].city = '';
        this.shList.shs[i].firm_city = '';
       }
      this.validateShareHolderEdit(i);
    }

    if ( type === 'shFirm' ) {
      this.shFirmList.shs[i].screen1Cities = filterCities;
      if (load === false ) {
       this.shFirmList.shs[i].firm_city = '';
      }
     this.validateShareHolderEdit(i , true );
   }

 }

 getProvincesForBen(type) {

  // tslint:disable-next-line:prefer-const
  let provinces = Object.assign({}, this.provinces);
  // tslint:disable-next-line:prefer-const
  let filterProvince: Array<IProvince> = [];
 // tslint:disable-next-line:prefer-const
 for ( let i in provinces ) {
  filterProvince.push( provinces[i]);
  }

  if ( type  === 'sh_benif' ) {

    this.sh_benif.screen1Provinces = filterProvince;
    this.validateShBenif();
  }
}

getDistrictsForBen(type, provinceName, load = false ) {

 // tslint:disable-next-line:prefer-const
 let districts = Object.assign({}, this.districts);
 // tslint:disable-next-line:prefer-const
 let filterDistricts: Array<IDistrict> = [];

 // tslint:disable-next-line:prefer-const
 for ( let i in districts ) {
     if ( districts[i].provinceName === provinceName ) {

      filterDistricts.push( districts[i]);
     }
 }

  if ( type  === 'sh_benif' ) {

    this.sh_benif.screen1Districts = filterDistricts;
    if (load === false ) {
      this.sh_benif.city = '';
      this.sh_benif.district = '';
    }
    this.validateShBenif();
  }
}

getCitiesForBen(type, districtName, load = false ) {

  // tslint:disable-next-line:prefer-const
  let cities = Object.assign({}, this.cities);
  // tslint:disable-next-line:prefer-const
  let filterCities: Array<ICity> = [];
  // tslint:disable-next-line:prefer-const
  for ( let i in cities ) {
      if ( cities[i].districtName === districtName ) {
        filterCities.push( cities[i]);
      }
  }

  if ( type === 'sh_benif') {
    this.sh_benif.screen1Cities = filterCities;
    if (load === false ) {
      this.sh_benif.city = '';
    }
    this.validateShBenif();
  }
 }

 getProvincesForBenEdit(type, i ) {

  // tslint:disable-next-line:prefer-const
  let provinces = Object.assign({}, this.provinces);
  // tslint:disable-next-line:prefer-const
  let filterProvince: Array<IProvince> = [];
 // tslint:disable-next-line:prefer-const
 for ( let j in provinces ) {
  filterProvince.push( provinces[j]);
  }
  if ( type  === 'sh_benif' ) {
    this.sh_benif.screen1Provinces = filterProvince;
    this.validateShBenifEdit(i);
  }
}

getDistrictsForBenEdit(type, i , provinceName, load = false ) {

 // tslint:disable-next-line:prefer-const
 let districts = Object.assign({}, this.districts);
 // tslint:disable-next-line:prefer-const
 let filterDistricts: Array<IDistrict> = [];

 // tslint:disable-next-line:prefer-const
 for ( let j in districts ) {
     if ( districts[j].provinceName === provinceName ) {

      filterDistricts.push( districts[j]);
     }
 }
  if ( type  === 'sh_benif' ) {
    this.sh_benif.screen1Districts = filterDistricts;
    if (load === false ) {
      this.sh_benif.city = '';
      this.sh_benif.district = '';
    }
    this.validateShBenifEdit(i);
  }
}

getCitiesForBenEdit(type, i , districtName, load = false ) {

  // tslint:disable-next-line:prefer-const
  let cities = Object.assign({}, this.cities);
  // tslint:disable-next-line:prefer-const
  let filterCities: Array<ICity> = [];
  // tslint:disable-next-line:prefer-const
  for ( let j in cities ) {
      if ( cities[j].districtName === districtName ) {
        filterCities.push( cities[j]);
      }
  }
  if ( type === 'sh_benif') {
    this.sh_benif.screen1Cities = filterCities;
    if (load === false ) {
      this.sh_benif.city = '';
    }
    this.validateShBenifEdit(i);
  }
 }

 selectStakeHolderType(stakeholder, type) {

  this.loadNICstakeholders = false;
  this.openAddressPart = false;

  if (stakeholder === 'sh') {
    // tslint:disable-next-line:max-line-length
    this.sh = { id: 0, status: null, groupAddedValue: '0', shareIssueClass: '',  showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', screen1Districts: [], screen1Cities: [], noOfShares: 0, shareholderType: 'natural', benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '' , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: '' };
    this.sh.type = type;

    this.shAlreadyExistMessage = '';
    if (this.sh.type !== 'local') {
      this.getProvincesForStakeHolder('sh');
      this.shNicLoaded = true;
    } else {
      this.getProvincesForStakeHolder('sh');
      this.shNicLoaded = false;
    }
    this.validateShareHolder();
  }
}


isShAlreadyExistAction(shType = 'local') {

  // tslint:disable-next-line:prefer-const
  let message = (shType === 'foreign') ?
    'This Shareholder Already Exists. Please Try a Different Passport number' :
    'This Shareholder Already Exists. Please Try a different NIC';

  if (this.isShAlreadyExist(shType)) {
    this.shAlreadyExistMessage = message;

    if (shType === 'local') {
      this.shNicLoaded = false;
    } else {
      this.shNicLoaded = true;
    }
  } else {
    this.shAlreadyExistMessage = '';

    if (shType === 'local') {
      this.checkNIC(3);
    }
  }

}

checkNIC(memberType: number = 3, secShBen = false) {


  this.directorNicLoaded = false;
  this.secNicLoaded = false;
  this.shNicLoaded = false;
  this.loadNICstakeholders = false;
  this.openAddressPart = false;

  // tslint:disable-next-line:prefer-const
  let checker =  this.sh.nic;
  let type =  this.sh.type;

  if (!checker) {

    this.shNicLoaded = false;
    this.loadNICstakeholders = false;
    this.openAddressPart = false;
    return false;
  }

  if (type !== 'local') {
    this.shNicLoaded = true;
    this.loadNICstakeholders = false;
    this.openAddressPart = false;

    return true;
  }

  const data = {
    companyId: this.companyId,
    nic: checker,
    memberType: memberType

  };

  this.callShareService.annualNICcheck(data)
    .subscribe(
      req => {

        this.loadNICstakeholders = false;
        this.openAddressPart = req['data']['openLocalAddress'];


          if (req['status'] && req['data']['member_count'] === 1) {

            // this.sh.title = 'Mr.';
            this.sh.title =  req['data']['title'];
            this.sh.firstname = req['data']['member_record'][0]['first_name'];
            this.sh.lastname = req['data']['member_record'][0]['last_name'];
            this.sh.email = req['data']['member_record'][0]['email'];
            this.sh.country = req['data']['member_record'][0]['passport_issued_country'];
            this.sh.nic = req['data']['member_record'][0]['nic'];
            this.sh.province = req['data']['address_record']['province'];
            this.sh.district = req['data']['address_record']['district'];
            this.sh.city = req['data']['address_record']['city'];
            this.sh.localAddress1 = req['data']['address_record']['address1'];
            this.sh.localAddress2 = req['data']['address_record']['address2'];
            this.sh.postcode = req['data']['address_record']['postcode'];

            this.sh.passport = req['data']['member_record'][0]['passport_no'];
            this.sh.phone = req['data']['member_record'][0]['telephone'];
            this.sh.mobile = req['data']['member_record'][0]['mobile'];
            this.sh.date = '';
            this.sh.occupation = req['data']['member_record'][0]['occupation'];

            this.getProvincesForStakeHolder('sh');
            this.getDistrictsForStakeholder('sh', this.sh.province, true );
            this.getCitiesForStakeholder( 'sh', this.sh.district, true );

            this.validateShareHolder();

            if (secShBen) {
              this.secNicLoaded = true;
            } else {
              this.shNicLoaded = true;
            }
            this.loadNICstakeholders = true;

          } else { // reset
            // tslint:disable-next-line:max-line-length
            this.sh = { id: 0, status: null, groupAddedValue: '0', shareIssueClass: '',  showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', screen1Districts: [], screen1Cities: [],  noOfShares: 0, shareholderType: 'natural', benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: ''};
            this.getProvincesForStakeHolder('sh');
            this.getDistrictsForStakeholder('sh', this.sh.province );
            this.getCitiesForStakeholder( 'sh', this.sh.district );
            if (secShBen) {
              this.secNicLoaded = true;
            } else {
              this.shNicLoaded = true;
            }
            this.sh.nic = checker;
            this.loadNICstakeholders = false;
            this.openAddressPart = false;
          }

          return true;


      },
      error => {
        console.log(error);
      }

    );
}

saveShareHolderRecord() {

  if (this.sh['type'] === 'local') {
    this.sh.country = 'Sri Lanka';
  }
  this.sh.benifiList = this.benifList;


  // tslint:disable-next-line:prefer-const
  let copy3 = Object.assign({}, this.sh);
  this.shList.shs.push(copy3);

  // tslint:disable-next-line:max-line-length
  this.sh = { id: 0, status: null, groupAddedValue: '0', shareIssueClass: '',  showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural',  screen1Districts: [], screen1Cities: [], benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '' , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: ''};

  this.validSh = false;
  this.submitShareholders('remove');
  this.benifList.ben = [];
}

submitNewShareholder() {
  if (this.sh['type'] === 'local') {
    this.sh.country = 'Sri Lanka';
  }
  let copy3 = Object.assign({}, this.sh);
  // tslint:disable-next-line:max-line-length
  this.sh = { id: 0, status: null, groupAddedValue: '0', shareIssueClass: '',  showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural',  screen1Districts: [], screen1Cities: [], benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '' , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: ''};

  const data = {
    companyId: this.companyId,
    shareholder: copy3,

  };
  this.callShareService.addNewShareholder(data)
    .subscribe(
      req => {
        this.loadData();
        this.changeProgressStatuses(2);
      },
      error => {
        this.changeProgressStatuses(2);
        console.log(error);
      }
  );

}

submitExistingShareholder(i, type= 'natural' ) {
  let copy3;
  if ( type === 'firm' ) {
    if (this.shFirmList.shs[i].type === 'local') {
      this.shFirmList.shs[i].country = 'Sri Lanka';
    }
    copy3 =  Object.assign({}, this.shFirmList.shs[i]);
  }
  if ( type === 'natural' ) {

    if (this.shList.shs[i].type === 'local') {
      this.shList.shs[i].country = 'Sri Lanka';
    }

    copy3 = Object.assign({}, this.shList.shs[i]);
  }

  const data = {
    companyId: this.companyId,
    shareholder: copy3,

  };
  this.callShareService.addNewExistShareholder(data)
    .subscribe(
      req => {
        this.loadData();
        this.changeProgressStatuses(2);
      },
      error => {
        this.changeProgressStatuses(2);
        console.log(error);
      }
  );

}


removeShareholder(id, type= 'natural' ) {

  const data = {
    companyId: this.companyId,
    shareholder_id: id,
    shareholder_type: type

  };
  this.callShareService.removeShareholder(data)
    .subscribe(
      req => {
        this.loadData();
        this.changeProgressStatuses(2);
      },
      error => {
        this.changeProgressStatuses(2);
        console.log(error);
      }
  );

}


removeShareClassRecord(id) {

  const data = {
    companyId: this.companyId,
    record_id: id,

  };
  this.callShareService.removeShareClassRecord(data)
    .subscribe(
      req => {
        this.loadData();
        this.changeProgressStatuses(1);
      },
      error => {
        this.changeProgressStatuses(1);
        console.log(error);
      }
  );

}

showCSVwrapperfunc() {
  this.showCSVwrapper = true;
  this.csvUploadMessage = '';
}
hideCSVwrapperfunc() {
  this.showCSVwrapper = false;
  this.csvUploadMessage = '';
}

resetSignParty() {
  this.singning_party_name = '';
}


} // end


