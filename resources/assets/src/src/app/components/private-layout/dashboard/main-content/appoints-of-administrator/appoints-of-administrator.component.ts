import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { APIForm34Connection } from './services/connections/APIForm34Connection';
import { count, distinct } from 'rxjs/operators';
import { GeneralService } from '../../../../../http/services/general.service';
import { HelperService } from '../../../../../http/shared/helper.service';
import { DataService } from '../../../../../storage/data.service';
import { PaymentService } from '../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../http/models/payment';
import { isNull } from '@angular/compiler/src/output/output_ast';
import { Form34Service } from './services/form34.service';
import { environment } from '../../../../../../environments/environment';
import { IDownloadDocs, IUploadDocs, IshareholderItems, ICallShares, ICallShare, IAdministrators, IAdministrator, ISignedDirectors, ISignedDirector, IProvince, IDistrict, ICity, Icountry} from './models/form34.model';
import { IcompanyType, IcompanyInfo, IloginUser, IloginUserAddress } from '../../../../../http/models/incorporation.model';
import { IDirector } from '../annual-return/models/annualReturn.model';
@Component({
  selector: 'app-appoints-of-administrator',
  templateUrl: './appoints-of-administrator.component.html',
  styleUrls: ['./appoints-of-administrator.component.scss']
})
export class AppointsOfAdministratorComponent implements OnInit, AfterViewInit {

  url: APIForm34Connection = new APIForm34Connection();

cipher_message: string;
paymentItems: Array<Item> = [];
paymentGateway: string = environment.paymentGateway;

payConfirm = false;
form34_payment = 0;
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

  document_confirm = false;

  moduleStatus = '';
  other_doc_name = '';
  formattedTodayValue = '';
  progress = {

    stepArr: [
      { label: 'Company Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Administrators', icon: 'fas fa-user-friends', status: '' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Pay/Complete', icon: 'fa fa-money-bill-alt', status: '' },
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

  provinces: Array<IProvince> = [];
  districts: Array<IDistrict> = [];
  cities: Array<ICity> = [];
  countries: Array<Icountry> = [];
  court_order_valid = false;

  uploadedList: {};
  uploadedListArrWithToken: {};

  enableStep1Submission = false;
  directorValitionMessage = '';
  directorAlreadyExistMessage = '';
  secAlreadyExistMessage = '';
  shAlreadyExistMessage = '';

  nicAlreadyExistShowMessage = false;

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


  registerRecordList: IAdministrators = {record: [] };
  // public record: IAdministrator = { id: null, showEditPane: 0, firstname: ''};
  // tslint:disable-next-line:max-line-length
  public record: IAdministrator = { id: 0, showEditPane: 0, type: 'local', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', officeProvince: '', officeDistrict: '', officeCity: '', officeAddress1: '', officeAddress2: '', officePostcode: '', screen1OfficeProvinces: [], screen1OfficeDistricts: [], screen1OfficeCities: [], court_case_no: '', court_date: '', court_name: '', court_discharged: '', court_penalty: '', court_period: '', appointed_by: '' };
  directorList: ISignedDirectors = {director: [] };
  director: ISignedDirector = { id: null, first_name: '', last_name: '', saved: false };

  savedDirectors = [];


  share_records_already_exists = true;

  admin_office_address1 = '';
  admin_office_address2 = '';
  admin_office_address3 = '';

  appointed_by = 'court_order';
  resolution_date = '';
  court_date = '';
  court_name = '';
  court_case_no = '';
  court_penalty = '';
  court_period = '';
  court_discharged = '';
  court_status = null;


  companyRegNumber = '';
  penalty_charge = 0;

  adminNIClist = [];

  constructor(
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private callShareService: Form34Service,
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
    this.formattedTodayValue = this.getFormatedToday();
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

          this.formattedTodayValue = this.getFormatedToday();

          this.moduleStatus = req['data']['moduleStatus'];

          if (this.moduleStatus === 'APPOINTMENT_OF_ADMIN_RESUBMIT' ) {
            this. document_confirm = true;
          }

          if ( !( this.moduleStatus === 'APPOINTMENT_OF_ADMIN_PROCESSING' || this.moduleStatus === 'APPOINTMENT_OF_ADMIN_RESUBMIT' ) ) {
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

          this.provinces = req['data']['pdc']['provinces'];
          this.districts = req['data']['pdc']['districts'];
          this.cities = req['data']['pdc']['cities'];
          this.countries = req['data']['countries'];

          this.companyInfo = req['data']['companyInfo'];
          this.loginUserInfo = req['data']['loginUser'];
          this.loginUserAddress = req['data']['loginUserAddress'];
          this.externalGlobComment = req['data']['external_global_comment'];

          this.registerRecordList.record = [];
          this.registerRecordList.record = req['data']['records'];

           // tslint:disable-next-line:prefer-const
           for (let i in  this.registerRecordList.record) {

            if (this.registerRecordList.record[i].nic) {
               this.adminNIClist.push(this.registerRecordList.record[i].nic.toUpperCase());
             }

            this.getProvincesForStakeHolderEdit(i);
            this.getDistrictsForStakeholderEdit(i, this.registerRecordList.record[i].province, true );
            this.getCitiesForStakeholderEdit(i, this.registerRecordList.record[i].district, true  );

            this.getProvincesForStakeHolderOfficeAddressEdit(i);
            this.getDistrictsForStakeholderOfficeAddresEdit(i, this.registerRecordList.record[i].officeProvince, true );
            this.getCitiesForStakeholderOfficeAddressEdit(i, this.registerRecordList.record[i].officeDistrict, true  );
          }



          this.docList = req['data']['downloadDocs'];
          this.uploadList = req['data']['uploadDocs'];
          this.uploadOtherList = req['data']['uploadOtherDocs'];
          this.allFilesUploaded = this.uploadList['uploadedAll'];

          this.admin_office_address1 = req['data']['admin_office_address1'];
          this.admin_office_address2 = req['data']['admin_office_address2'];
          this.admin_office_address3 = req['data']['admin_office_address3'];

          this.court_status = req['data']['court_data']['court_status'];
          this.court_name = req['data']['court_data']['court_name'];
          this.court_date = req['data']['court_data']['court_date'];
          this.court_case_no = req['data']['court_data']['court_case_no'];
          this.court_discharged = req['data']['court_data']['court_discharged'];
          this.court_penalty = req['data']['court_data']['court_penalty'];
          this.court_period = req['data']['court_data']['court_period'];

          this.directorList.director = req['data']['directorList'];

          this.form34_payment = (req['data']['form34_payment']) ? parseFloat( req['data']['form34_payment'] ) : 0;
          this.vat = (req['data']['vat']) ? parseFloat( req['data']['vat'] ) : 0;
          this.other_tax = (req['data']['other_tax']) ? parseFloat( req['data']['other_tax'] ) : 0;
          this.convinienceFee = (req['data']['convinienceFee']) ? parseFloat( req['data']['convinienceFee'] ) : 0;
          this.penalty_charge = parseFloat( req['data']['penalty_charge'] );

          var penaltyval = this.court_status === 'yes' ? 0 : this.penalty_charge;

          this.total_wihtout_vat_tax = this.form34_payment + penaltyval;

          this.other_taxVal = ( this.total_wihtout_vat_tax * this.other_tax ) / 100;
          this.vatVal = ( this.total_wihtout_vat_tax + this.other_taxVal ) * this.vat / 100;
          this.convinienceFeeVal = ( this.total_wihtout_vat_tax + this.other_taxVal  + this.vatVal ) * this.convinienceFee / 100;
          this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;

          this.changeProgressStatuses(this.stepOn);
          this.getProvincesForStakeHolder();
          this.getProvincesForStakeHolderOfficeAddress();
          this.validateStakeholderOnload();
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
            this.loadData();
            this.other_doc_name = '';
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


showToggleRecord(recId = 0) {

  // tslint:disable-next-line:prefer-const
  for (let i in this.registerRecordList.record) {
    if (this.registerRecordList.record[i]['id'] === recId) {
      this.registerRecordList.record[i]['showEditPane'] = this.registerRecordList.record[i]['showEditPane'] === recId ? null : recId;
      return true;
    }
  }
}

resetRecord() {
  // tslint:disable-next-line:max-line-length
  this.record = { id: 0, showEditPane: 0, type: 'local', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', officeProvince: '', officeDistrict: '', officeCity: '', officeAddress1: '', officeAddress2: '', officePostcode: '', screen1OfficeProvinces: [], screen1OfficeDistricts: [], screen1OfficeCities: [], court_case_no: '', court_date: '', court_discharged: '', court_name: '', court_penalty: '', court_period: '', appointed_by: '' };
  this.getProvincesForStakeHolder();
  this.getProvincesForStakeHolderOfficeAddress();
}

private validateStakeholderOnload(){

  // tslint:disable-next-line:prefer-const
  for (let i in this.registerRecordList.record) {
    if ( ! this.validateRecordEdit(i)) {
      this.enableStep2Submission = false;
      this.enableStep2SubmissionEdit = false;
      return false;
    }

  }

  this.enableStep2Submission = true;
  this.enableStep2SubmissionEdit = true;
  return true;
}



checkNICrecordexistEdit(i) {

  if ( this.adminNIClist.includes(this.registerRecordList.record[i].nic.toUpperCase())  ) {
         alert('Admininstrator NIC already exists.');
  }
}

validateRecordEdit(i) {

  let directorRow = this.registerRecordList.record[i];

  if (directorRow.type === 'local') {

    if (!
      (
        directorRow.nic && this.validateNIC(directorRow.nic) &&
      // this.director.title &&
      directorRow.email && this.validateEmail(directorRow.email) &&
      directorRow.firstname &&
      directorRow.lastname &&
      directorRow.province &&
      directorRow.district &&
      directorRow.city &&
      directorRow.mobile && this.phonenumber(directorRow.mobile, directorRow.type) &&
      directorRow.localAddress1 &&
      directorRow.localAddress2 &&
      directorRow.postcode &&
    //  directorRow.date &&

      directorRow.officeProvince &&
      directorRow.officeDistrict &&
      directorRow.officeCity &&
      directorRow.officeAddress1 &&
      directorRow.officeAddress2 &&
      directorRow.officePostcode &&
      directorRow.appointed_by &&

      (
        ( directorRow.appointed_by === 'resolution' && directorRow.resolution_date) ||
         (directorRow.appointed_by === 'court_order' && directorRow.court_case_no && directorRow.court_date && directorRow.court_name &&  ( directorRow.court_penalty ? parseFloat(directorRow.court_penalty) >= 0 : true ) )

      )
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

  if (directorRow.type === 'foreign') {

    if (!(directorRow.passport && directorRow.passport_issued_country &&
      // this.director.title &&
      directorRow.email && this.validateEmail(directorRow.email) &&
      directorRow.firstname &&
      directorRow.lastname &&
      directorRow.forProvince &&
      directorRow.forCity &&
      directorRow.mobile && this.phonenumber(directorRow.mobile, directorRow.type) &&
      directorRow.forAddress1 &&
      ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ?
       true : ( directorRow.province && directorRow.district && directorRow.city && directorRow.localAddress1 &&  directorRow.postcode )) &&
      directorRow.country &&
      ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS')  ? true :  directorRow.forPostcode ) &&
    //  directorRow.date &&
      directorRow.officeProvince &&
      directorRow.officeDistrict &&
      directorRow.officeCity &&
      directorRow.officeAddress1 &&
      directorRow.officeAddress2 &&
      directorRow.officePostcode &&
      directorRow.appointed_by &&

      (
        ( directorRow.appointed_by === 'resolution' && directorRow.resolution_date) ||
         (directorRow.appointed_by === 'court_order' && directorRow.court_case_no && directorRow.court_date && directorRow.court_name && directorRow.court_penalty && parseFloat(directorRow.court_penalty) && directorRow.court_period)

      )
    )) {

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

}
checkNICrecordexist() {
  console.log(this.adminNIClist);
  console.log(this.record.nic.toUpperCase());
  if ( this.adminNIClist.includes(this.record.nic.toUpperCase())  ) {
       //  alert('Admininstrator NIC already exists.');
       console.log('fuck');
         this.nicAlreadyExistShowMessage = true;
  } else {
        this.nicAlreadyExistShowMessage = false;
  }
}

validateRecord() {

  if (this.record.type === 'local') {

    if (!
      (
        this.record.nic && this.validateNIC(this.record.nic) &&
        ( this.adminNIClist.includes(this.record.nic.toUpperCase()) === false ) &&
        this.record.email && this.validateEmail(this.record.email) &&
        this.record.firstname &&
        this.record.lastname &&
        this.record.province &&
        this.record.district &&
        this.record.city &&
        this.record.mobile && this.phonenumber(this.record.mobile, this.record.type) &&
        this.record.localAddress1 &&
        this.record.localAddress2 &&
        this.record.postcode &&
      //  this.record.date &&

        this.record.officeProvince &&
        this.record.officeDistrict &&
        this.record.officeCity &&
        this.record.officeAddress1 &&
        this.record.officeAddress2 &&
        this.record.officePostcode &&
        this.record.appointed_by &&

       (
         ( this.record.appointed_by === 'resolution' && this.record.resolution_date) ||
          (this.record.appointed_by === 'court_order' && this.record.court_case_no && this.record.court_date && this.record.court_name && (this.record.court_penalty ?  parseFloat(this.record.court_penalty) >= 0 : true ) )

       )

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

  if (this.record.type === 'foreign') {

    if (!(this.record.passport && this.record.passport_issued_country &&
      this.record.email && this.validateEmail(this.record.email) &&
      this.record.firstname &&
      this.record.lastname &&

      ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ?
       true : ( this.record.province && this.record.district && this.record.city && this.record.localAddress1 &&  this.record.postcode )) &&

      this.record.forProvince &&
      this.record.forCity &&
      this.record.country &&
      this.record.mobile && this.phonenumber(this.record.mobile, this.record.type) &&
      this.record.forAddress1 &&
      ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS')  ? true :  this.record.forPostcode ) &&
    //  this.record.date &&
      this.record.officeProvince &&
      this.record.officeDistrict &&
      this.record.officeCity &&
      this.record.officeAddress1 &&
      this.record.officeAddress2 &&
      this.record.officePostcode &&
      this.record.appointed_by &&

      (
        ( this.record.appointed_by === 'resolution' && this.record.resolution_date) ||
         (this.record.appointed_by === 'court_order' && this.record.court_case_no && this.record.court_date && this.record.court_name && this.record.court_penalty && parseFloat(this.record.court_penalty) && this.record.court_period)

      )
    )) {

      this.shareRecordValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validShareRecordItem = false;
      return false;

    } else {
      this.shareRecordValitionMessage = '';
      this.validShareRecordItem = true;
      return true;

    }

  }



}


 saveRecord() {

  // tslint:disable-next-line:prefer-const
  let copy = Object.assign({}, this.record);
  this.registerRecordList.record.push(copy);

  // tslint:disable-next-line:max-line-length
  this.record = { id: 0, showEditPane: 0, type: 'local', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', officeProvince: '', officeDistrict: '', officeCity: '', officeAddress1: '', officeAddress2: '', officePostcode: '', screen1OfficeProvinces: [], screen1OfficeDistricts: [], screen1OfficeCities: [] };
  this.validShareRecordItem = false;

  this.getProvincesForStakeHolder();
  this.getProvincesForStakeHolderOfficeAddress();

  this.submitRecord('remove');

}

submitRecord(action = '') {

  const data = {
    companyId: this.companyId,
    loginUser: this.loginUserEmail,
    records: this.registerRecordList,
    directors: this.directorList,
    admin_office_address1:  this.admin_office_address1,
    admin_office_address2: this.admin_office_address2,
    admin_office_address3: this.admin_office_address3,
    court_date: this.court_date,
    resolution_date: this.resolution_date,
    appointed_by: this.appointed_by,
    court_name : this.court_name,
    court_case_no : this.court_case_no,
    court_period : this.court_period,
    court_penalty : this.court_penalty,
    court_discharged: this.court_discharged
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

removeRecord(i: number, recId: number = 0) {

  if ( !confirm('Are you sure you want to remove this record?') ) {
    return true;
  }

  this.registerRecordList.record.splice(i, 1);
  if (!recId) {
    return true;
  }
  this.submitRecord('remove');

}


/**************End SHARE Functions*******************/


  pay() {


    this.paymentItems.push(
      {
          fee_type: 'PAYMENT_APPOINTMENT_OF_ADMIN_FORM34',
          description: 'Appoint of Administrator - Form 34',
          quantity: 1,
      }

    );

    const buy: IBuy = {
      module_type: 'MODULE_APPOINTMENT_OF_ADMIN',
      module_id: this.requestId.toString(),
      description: 'Appoint of Administrator',
      item: this.paymentItems,
      extraPay: null,
      penalty: ( this.court_status !== 'yes') ? this.penalty_charge.toString() : null
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

  savedcheck() {
    console.log(this.directorList.director);
  }



  /*********util functions  */
  private validateEmail(email) {
    if (!email) { return false; }
    // tslint:disable-next-line:prefer-const
   // let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
     // tslint:disable-next-line:prefer-const
  // let re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

   // let re = /^[A-Za-z0-9]([a-zA-Z0-9]+([_.-][a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,4})$/;
   /**
    *  source : https://stackoverflow.com/questions/201323/how-to-validate-an-email-address-using-a-regular-expression?rq=1
    *
    **/
   let re = /^(?:[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/;
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

  getProvincesForStakeHolder() {

    // tslint:disable-next-line:prefer-const
    let provinces = Object.assign({}, this.provinces);
    // tslint:disable-next-line:prefer-const
    let filterProvince: Array<IProvince> = [];
   // tslint:disable-next-line:prefer-const
   for ( let i in provinces ) {
    filterProvince.push( provinces[i]);
    }
    this.record.screen1Provinces = filterProvince;
    this.validateRecord();
  }

  getDistrictsForStakeholder( provinceName, load = false ) {

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

    this.record.screen1Districts = filterDistricts;

     if (load === false ) {
      this.record.city = '';
      this.record.district = '';
     }

    this.validateRecord();
  }

  getCitiesForStakeholder(districtName, load = false ) {

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
    this.record.screen1Cities = filterCities;
    if (load === false ) {
        this.record.city = '';
      }
    this.validateRecord();
   }

   ////// edit
   getProvincesForStakeHolderEdit(i) {

    // tslint:disable-next-line:prefer-const
    let provinces = Object.assign({}, this.provinces);
    // tslint:disable-next-line:prefer-const
    let filterProvince: Array<IProvince> = [];
   // tslint:disable-next-line:prefer-const
   for ( let j in provinces ) {
    filterProvince.push( provinces[j]);
    }
    this.registerRecordList.record[i].screen1Provinces = filterProvince;
    this.validateRecordEdit(i);

  }

  getDistrictsForStakeholderEdit( i , provinceName, load = false ) {

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
   this.registerRecordList.record[i].screen1Districts = filterDistricts;

  if (load === false ) {
    this.registerRecordList.record[i].city = '';
    this.registerRecordList.record[i].district = '';
  }

  this.validateRecordEdit(i);
  }

  getCitiesForStakeholderEdit(i, districtName, load = false ) {

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

    this.registerRecordList.record[i].screen1Cities = filterCities;
    if (load === false ) {
      this.registerRecordList.record[i].city = '';
    }
    this.validateRecordEdit(i);

   }


  getProvincesForStakeHolderOfficeAddress() {

    // tslint:disable-next-line:prefer-const
    let provinces = Object.assign({}, this.provinces);
    // tslint:disable-next-line:prefer-const
    let filterProvince: Array<IProvince> = [];
   // tslint:disable-next-line:prefer-const
   for ( let i in provinces ) {
    filterProvince.push( provinces[i]);
    }
    this.record.screen1OfficeProvinces = filterProvince;
    console.log(this.record.screen1OfficeProvinces);
    this.validateRecord();
  }

  getDistrictsForStakeholderOfficeAddress( provinceName, load = false ) {

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

    this.record.screen1OfficeDistricts = filterDistricts;

     if (load === false ) {
      this.record.officeCity = '';
      this.record.officeDistrict = '';
     }

    this.validateRecord();
  }

  getCitiesForStakeholderOfficeAddress(districtName, load = false ) {

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
    this.record.screen1OfficeCities = filterCities;
    if (load === false ) {
        this.record.officeCity = '';
      }
    this.validateRecord();
   }

   ////// edit
   getProvincesForStakeHolderOfficeAddressEdit(i) {

    // tslint:disable-next-line:prefer-const
    let provinces = Object.assign({}, this.provinces);
    // tslint:disable-next-line:prefer-const
    let filterProvince: Array<IProvince> = [];
   // tslint:disable-next-line:prefer-const
   for ( let j in provinces ) {
    filterProvince.push( provinces[j]);
    }
    this.registerRecordList.record[i].screen1OfficeProvinces = filterProvince;
    this.validateRecordEdit(i);

  }

  getDistrictsForStakeholderOfficeAddresEdit( i , provinceName, load = false ) {

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
   this.registerRecordList.record[i].screen1OfficeDistricts = filterDistricts;

  if (load === false ) {
    this.registerRecordList.record[i].officeCity = '';
    this.registerRecordList.record[i].officeDistrict = '';
  }

  this.validateRecordEdit(i);
  }

  getCitiesForStakeholderOfficeAddressEdit(i, districtName, load = false ) {

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

    this.registerRecordList.record[i].screen1OfficeCities = filterCities;
    if (load === false ) {
      this.registerRecordList.record[i].officeCity = '';
    }
    this.validateRecordEdit(i);

   }

   selectStakeHolderType(type) {


    this.record = { id: 0, showEditPane: 0, type: 'local', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '' };
    this.record.type = type;

    if (this.record.type !== 'local') {

        this.getProvincesForStakeHolder();
        this.getProvincesForStakeHolderOfficeAddress();
        this.record.nic = '';
      } else {
        this.getProvincesForStakeHolder();
        this.getProvincesForStakeHolderOfficeAddress();

      }
      this.validateRecord();

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

  validateCourtOrder() {

    if (this.penalty_charge <= 0 ) {
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




