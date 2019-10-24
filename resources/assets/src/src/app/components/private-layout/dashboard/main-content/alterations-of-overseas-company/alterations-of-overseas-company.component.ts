import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { IDirectors, IDirector, ISecretories, ISecretory, IShareHolders, IShareHolder, IShareHolderBenif, IShareHolderBenifList, IProvince, IDistrict, ICity, IObjective, IGnDivision, IObjectiveRow, IObjectiveCollection } from '../../../../../http/models/stakeholder.model';
import { APIForm35Connection } from './services/connections/APIForm35Connection';
import { IcompanyInfo, IcompanyAddress, IcompanyType, IcompnayTypesItem, IcompanyObjective, IloginUserAddress, IloginUser, IcoreShareGroup, Icountry, IcompanyForAddress, IirdInfo } from '../../../../../http/models/incorporation.model';
import { count, distinct } from 'rxjs/operators';
import { GeneralService } from '../../../../../http/services/general.service';
import { HelperService } from '../../../../../http/shared/helper.service';
import { DataService } from '../../../../../storage/data.service';
import { PaymentService } from '../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../http/models/payment';
import { isNull } from '@angular/compiler/src/output/output_ast';
import { Form35Service } from './services/form35.service';
import { environment } from '../../../../../../environments/environment';
import { IShareRegisters, IShareRegister, IAnnualRecords, IAnnualRecord, IAnnualAuditors, IAnnualAuditor, IAnnualCharges, IAnnualCharge, IDownloadDocs, IUploadDocs, IShareRecords, IShareRecord, ICompanyOldRecord, IbulkShareholderBulkInfo, IAnnualReturnDatesInfo, IAlterOptions, IremoveUserInfo } from './models/form35.model';
@Component({
  selector: 'app-alterations-of-overseas-company',
  templateUrl: './alterations-of-overseas-company.component.html',
  styleUrls: ['./alterations-of-overseas-company.component.scss']
})
export class AlterationsOfOverseasCompanyComponent implements OnInit, AfterViewInit {

  url: APIForm35Connection = new APIForm35Connection();

cipher_message: string;
paymentItems: Array<Item> = [];
paymentGateway: string = environment.paymentGateway;

payConfirm = false;
form15_payment = 0;
form45_payment = 0;
form46_payment = 0;
form45_payment_enable = false;
form46_payment_enable = false;
vat = 0;
vatVal = 0;

other_tax = 0;
other_taxVal = 0;

formattedTodayValue = '';

convinienceFee = 0;
convinienceFeeVal = 0;

isReasubmit = false;

document_confirm = false;

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

  alterOptions: Array<IAlterOptions> = [];
  alterType: Array<string> = [];

  progress = {

    stepArr: [
      { label: 'Company Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Company Addresses', icon: 'fas fa-map-marked-alt', status: '' },
      { label: 'Directors', icon: 'fas fa-users', status: '' },
      { label: 'Power of attorney', icon: 'fas fa-users', status: '' },
      { label: 'Amended documents', icon: 'fa fa-file', status: '' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Pay and Complete', icon: 'fa fa-money-bill-alt', status: '' },
    ],

    progressPercentage: '14.29%'

  };

  compayType: IcompanyType = { key: '', value: '', id: null, value_si: '', value_ta: '' };
  externalGlobComment = '';
  postFixEn = ''; postFixSi = ''; postFixTa = '';
  companyAddress: IcompanyAddress = {
    address1: '', address2: '', gn_division: '',  city: '', country: '', created_at: '', district: '', id: 0, postcode: '', province: '', updated_at: ''
  };
  requestAddress: IcompanyAddress = {
    address1: '', address2: '', gn_division: '',  city: '', country: '', created_at: '', district: '', id: 0, postcode: '', province: '', updated_at: ''
  };
  hasRequestAddress = false;
  companyForAddress: IcompanyForAddress = {
    address1: '', address2: '', city: '', country: '', created_at: '', district: '', province: '', updated_at: '', postcode: ''
  };
  requestForAddress: IcompanyForAddress = {
    address1: '', address2: '', city: '', country: '', created_at: '', district: '', province: '', updated_at: '', postcode: ''
  };
  hasRequestForAddress = false;

  requestForAddress_change_date = '';
  requestAddress_change_date = '';

  companyInfo: IcompanyInfo = {
    abbreviation_desc: '', address_id: null, created_at: null, created_by: null, email: '', id: null, name: '', name_si: '', name_ta: '', postfix: '', status: null, type_id: null, updated_at: null, objective1: null, objective2: null, objective3: null, objective4: null, objective5: null, otherObjective: '', incorporation_at: ''
  };
  oldCompanyInfo: ICompanyOldRecord = {
    old_postfix: '', old_type_id: null, oldName: ''
  };
  companyRegistrationNumber = '';
  loginUserInfo: IloginUser;  loginUserAddress: IloginUserAddress;
  countries: Array<Icountry> = [];
  provinces: Array<IProvince> = [];
  districts: Array<IDistrict> = [];
  cities: Array<ICity> = [];
  gns: Array<IGnDivision> = [];
  screen1Provinces: Array<IProvince> = [];
  screen1Districts: Array<IDistrict> = [];
  screen1Cities: Array<ICity> = [];
  screen1Gns: Array<IGnDivision> = [];
  coreShareGroups: Array<IcoreShareGroup> = [];
  isGuarantyCompany = false;
  isShareholderLable = 'Shareholder';

  docList: IDownloadDocs = { docs: [] };
  uploadList: IUploadDocs = { docs: [] };
  otherUploadList: IUploadDocs = { docs: [] };
  other_doc_name = '';

  additionalUploadList: IUploadDocs = {docs: [] };

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
  loadForAddressCompany = false;

  validShareRegister = false;
  validAnnualRecord = false;
  validAnnualAuditorRecord = false;
  validAnnualChargeRecord = false;
  validShareRecordItem = false;

  annualReturnDates: IAnnualReturnDatesInfo = {incorporation_date: '', is_incorporation_date_as_last_annual_return: false, last_year_annual_return_date: '', this_year_annual_return_date: ''};

  allFilesUploaded = false;
  otherFilesUploadedAtLeast = false;
  other_docs_change_date = '';
  // director interfaces
  directorList: IDirectors = { directors: [] };
  // tslint:disable-next-line:max-line-length
  director: IDirector = { id: 0, secRegDate: '', showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec: true, existing_record_id: null, other_relevent: '', changedate: '' };

  // change director interfaces
  changeDirectorList: IDirectors = { directors: [] };
  // tslint:disable-next-line:max-line-length
  changeDirector: IDirector = { id: 0, secRegDate: '', showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec: true, existing_record_id: null, other_relevent: '', changedate: ''  };

  // sercretory interfaces
  secList: ISecretories = { secs: [] };
  secFirmList: ISecretories = { secs: [] };
  // tslint:disable-next-line:max-line-length
  sec: ISecretory = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', benifOwnerType : 'local', screen1Provinces: [], screen1Districts: [], screen1Cities: [], firm_city: '' , firm_district: '', firm_province: '', firm_country: '', validateSecShBenifInEdit : false, secBenifList : { ben : [] }, forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '' , passport_issued_country: '', firm_date: '', existing_record_id: null, other_relevent: '' , changedate: '' };

  // sercretory interfaces
  changeSecList: ISecretories = { secs: [] };
  changeSecFirmList: ISecretories = { secs: [] };
  // tslint:disable-next-line:max-line-length
  changeSec: ISecretory = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', benifOwnerType : 'local', screen1Provinces: [], screen1Districts: [], screen1Cities: [], firm_city: '' , firm_district: '', firm_province: '', firm_country: '', validateSecShBenifInEdit : false, secBenifList : { ben : [] }, forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '' , passport_issued_country: '', firm_date: '' , existing_record_id: null, other_relevent: '' , changedate: '' };

  // share holder interfaces
  shList: IShareHolders = { shs: [] };
  shFirmList: IShareHolders = { shs: [] };
  // tslint:disable-next-line:max-line-length
  public sh: IShareHolder = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , firm_city: '', firm_district: '', firm_province: '',  benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null }, passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: '' };

  shList_inactive: IShareHolders = { shs: [] };
  shFirmList_inactive: IShareHolders = { shs: [] };
  // tslint:disable-next-line:max-line-length
  public sh_inactive: IShareHolder = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , firm_city: '', firm_district: '', firm_province: '',  benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null }, passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: '' };

  benifList: IShareHolderBenifList = { ben: [] };
  public sh_benif: IShareHolderBenif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] };

  secBenifList: IShareHolderBenifList = { ben: [] };
  public sec_sh_benif: IShareHolderBenif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '',  screen1Provinces: [], screen1Districts: [], screen1Cities: [] };

  directorToShareHolder = false;
  shareHolderToDirector = false;
  loadNICstakeholders = false;
  openAddressPart = false;

  shareRegisterList: IShareRegisters = {sr: [] };
  public shareRegister: IShareRegister = {id: null, showEditPane: 0, description: '', address_id: null, foreign_address_id: null, address_type: 'local', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forProvince: '', forCity: '', forPostcode: '', country: 'Sri Lanka'};

  annualRecordList: IAnnualRecords = {rec: [] };
  public annualRecord: IAnnualRecord = {id: null, showEditPane: 0, description: '', address_id: null, foreign_address_id: null, address_type: 'local', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forProvince: '', forCity: '', forPostcode: '', country: 'Sri Lanka'};

  auditorList: IAnnualAuditors = {member: [] };
  public auditor: IAnnualAuditor = {id: null, showEditPane: 0, first_name: '', last_name: '', address_id: null, foreign_address_id: null, address_type: 'local', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forProvince: '', forCity: '', forPostcode: '', country: 'Sri Lanka'};

  chargeList: IAnnualCharges = {ch: [] };
  public charge: IAnnualCharge = {id: null, showEditPane: 0, name: '', date: '', amount: '', description: '' , address_id: null, foreign_address_id: null, address_type: 'local', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forProvince: '', forCity: '', forPostcode: '', country: 'Sri Lanka'};

  shareList: IShareRecords = {share: [] };
  public shareItem: IShareRecord = {id: null, showEditPane: 0, issue_type_as_cash: true, issue_type_as_non_cash: false , share_class: '', share_consideration_value_paid: '', no_of_shares: '', share_value: '', share_consideration: '' , shares_issued_for_cash: '', shares_issued_for_non_cash: '', shares_called_on: ''};

  bulkShareholderInfo: IbulkShareholderBulkInfo = {  screen1Provinces: [], screen1Districts: [], screen1Cities: [], province: '', district: '', city: '', country: 'Sri Lanka', title: ''};
  directors_already_exists = true;
  secs_already_exists = true;
  sh_already_exists = true;
  sh_inactive_already_exists = true;
  share_register_already_exists = true;
  annual_records_already_exists = true;
  annual_charges_already_exists = true;
  share_records_already_exists = true;

  amount_calls_recieved =  '';
  amount_calls_unpaid = '';
  amount_calls_forfeited =  '';
  amount_calls_purchased =  '';
  amount_calls_redeemed = '';

  resolution_date = '';
  example_shareholder_bulk_data = '';
  shareholder_bulk_format = '';
  example_member_bulk_data = '';
  member_bulk_format = '';
  example_ceased_shareholder_bulk_data = '';
  ceased_shareholder_bulk_format = '';
  example_ceased_member_bulk_data = '';
  ceased_member_bulk_format = '';

  article_date = '';
  memorandum_date = '';
  statute_date = '';
  charter_date = '';

  article_file_upload = '';
  statute_file_upload = '';
  charter_file_upload = '';
  memorandum_file_upload = '';

  valide_other_doc_upload = false;

  court_status = '';
  court_date = '';
  court_name = '';
  court_case_no = '';
  court_penalty = '';
  court_period = '';
  court_discharged = '';

  court_order_valid = false;

  penalty = 0;

  removeStakeholderInfo: IremoveUserInfo = {reason: '', effective_date: '', other_option_reason: ''};
  valideRemoveStakeholderInfo = false;
  removeStakeholderId = null;
  reoveStakeholderType = '';
  removeStakeholderUserType = '';
  removeStakeholderAppointmentDate = '';

  constructor(
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private annualService: Form35Service,
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

    this.loadHeavyData();

  }


  resetRemoveStakeholderInfo() {
    this.removeStakeholderInfo = {reason: '', other_option_reason: '', effective_date: ''};
  }

  validateRemoveStakeholderInfo() {

     if (
       this.removeStakeholderInfo.effective_date &&
       this.removeStakeholderInfo.reason &&
       (this.removeStakeholderInfo.reason === 'Other' ? this.removeStakeholderInfo.other_option_reason : true )
     ) {
        this.valideRemoveStakeholderInfo = true;
     } else {
        this.valideRemoveStakeholderInfo = false;
     }
  }

  getProvincesForScreen1() {

    // tslint:disable-next-line:prefer-const
    let provinces = Object.assign({}, this.provinces);
    // tslint:disable-next-line:prefer-const
    let filterProvince: Array<IProvince> = [];
   // tslint:disable-next-line:prefer-const
   for ( let i in provinces ) {
    filterProvince.push( provinces[i]);
    }
    this.screen1Provinces = filterProvince;
    this.step1Validation();
  }

  getDistrictsForScreen1(provinceName, load = false ) {

    this.screen1Gns = [];
    this.screen1Cities = [];
    this.screen1Districts = [];

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

    this.screen1Districts = filterDistricts;

    if (load === false ) {
      this.requestAddress.district = '';
      this.requestAddress.city = '';
      this.requestAddress.gn_division = '';
    }

    this.step1Validation();
  }

  getCitiesForScreen1(districtName, load = false ) {

    this.screen1Gns = [];
    this.screen1Cities = [];

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
     this.screen1Cities = filterCities;
     if (load === false ) {
      this.requestAddress.city = '';
      this.requestAddress.gn_division = '';
    }
     this.step1Validation();
   }


   getGnsForScreen1(cityName, load = false ) {

    this.screen1Gns = [];

    // tslint:disable-next-line:prefer-const
    let gns = Object.assign({}, this.gns);
    // tslint:disable-next-line:prefer-const
    let filterGns: Array<IGnDivision> = [];
    // tslint:disable-next-line:prefer-const
    for ( let i in gns ) {
        if ( gns[i].cityName === cityName ) {
          filterGns.push( gns[i]);
        }
    }
     this.screen1Gns = filterGns;
     if (load === false ) {
      this.requestAddress.gn_division = '';
    }
     this.step1Validation();
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

    if ( type === 'director' ) {
       this.director.screen1Provinces = filterProvince;
       this.validateDirector();
    }
    if ( type === 'sec' ) {
        this.sec.screen1Provinces = filterProvince;
        this.validateSec();
    }
    if ( type === 'sh' ) {
        this.sh.screen1Provinces = filterProvince;
       this.validateShareHolder();
    }
    if ( type === 'sh_inactive' ) {
      this.sh_inactive.screen1Provinces = filterProvince;
     this.validateShareHolder_inactive();
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

   if ( type === 'director' ) {
    this.director.screen1Districts = filterDistricts;

     if (load === false ) {
      this.director.city = '';
      this.director.district = '';
     }

    this.validateDirector();
    }
    if ( type === 'sec' ) {
      this.sec.screen1Districts = filterDistricts;

      if (load === false ) {
        this.sec.city = '';
        this.sec.district = '';
        this.sec.firm_city = '';
        this.sec.firm_district = '';
       }
      this.validateSec();
      this.validateSecForiegn();

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
    if ( type === 'sh_inactive' ) {
      this.sh_inactive.screen1Districts = filterDistricts;
      if (load === false ) {
       this.sh_inactive.city = '';
       this.sh_inactive.district = '';
       this.sh_inactive.firm_city = '';
       this.sh_inactive.firm_district = '';
      }
     this.validateShareHolder_inactive();
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
     if ( type === 'director' ) {
      this.director.screen1Cities = filterCities;
      if (load === false ) {
        this.director.city = '';
       }
      this.validateDirector();
      }
      if ( type === 'sec' ) {
        this.sec.screen1Cities = filterCities;
        if (load === false ) {
          this.sec.city = '';
          this.sec.firm_city = '';
         }
        this.validateSec();
        this.validateSecForiegn();
      }
      if ( type === 'sh' ) {
         this.sh.screen1Cities = filterCities;
         if (load === false ) {
          this.sh.city = '';
          this.sh.firm_city = '';
         }
        this.validateShareHolder();
      }
      if ( type === 'sh_inactive' ) {
        this.sh_inactive.screen1Cities = filterCities;
        if (load === false ) {
         this.sh_inactive.city = '';
         this.sh_inactive.firm_city = '';
        }
       this.validateShareHolder_inactive();
     }
   }

   ////// edit
   getProvincesForStakeHolderEdit(type, i) {

    // tslint:disable-next-line:prefer-const
    let provinces = Object.assign({}, this.provinces);
    // tslint:disable-next-line:prefer-const
    let filterProvince: Array<IProvince> = [];
   // tslint:disable-next-line:prefer-const
   for ( let j in provinces ) {
    filterProvince.push( provinces[j]);
    }

    if ( type === 'director' ) {
       this.directorList.directors[i].screen1Provinces = filterProvince;
       this.validateDirectorEdit(i);
    }
    if ( type === 'change_director' ) {
      this.changeDirectorList.directors[i].screen1Provinces = filterProvince;
      this.validateChangeDirectorEdit(i);
   }
    if ( type === 'sec' ) {
        this.secList.secs[i].screen1Provinces = filterProvince;
        this.validateSecEdit(i);
    }
    if ( type === 'change_sec' ) {
      this.changeSecList.secs[i].screen1Provinces = filterProvince;
      this.validateChangeSecEdit(i);
   }
    if ( type === 'secFirm' ) {
      this.secFirmList.secs[i].screen1Provinces = filterProvince;
      this.validateSecEditForSecFirm(i);
    }

    if ( type === 'change_secFirm' ) {
      this.changeSecFirmList.secs[i].screen1Provinces = filterProvince;
      this.validateSecEditForSecChangeFirm(i);
    }
    if ( type === 'sh' ) {
        this.shList.shs[i].screen1Provinces = filterProvince;
       this.validateShareHolderEdit(i);
    }
    if ( type === 'shFirm' ) {
      this.shFirmList.shs[i].screen1Provinces = filterProvince;
     this.validateShareHolderEdit(i, true);
    }
    if ( type === 'sh_inactive' ) {
      this.shList_inactive.shs[i].screen1Provinces = filterProvince;
     this.validateShareHolder_inactiveEdit(i);
    }
    if ( type === 'shFirm_inactive' ) {
      this.shFirmList_inactive.shs[i].screen1Provinces = filterProvince;
    this.validateShareHolder_inactiveEdit(i, true);
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

   if ( type === 'director' ) {
    this.directorList.directors[i].screen1Districts = filterDistricts;

     if (load === false ) {
      this.directorList.directors[i].city = '';
      this.directorList.directors[i].district = '';
     }

    this.validateDirectorEdit(i);
    }

    if ( type === 'change_director' ) {
      this.changeDirectorList.directors[i].screen1Districts = filterDistricts;

       if (load === false ) {
        this.changeDirectorList.directors[i].city = '';
        this.changeDirectorList.directors[i].district = '';
       }
      this.validateChangeDirectorEdit(i);
      }

    if ( type === 'sec' ) {
      this.secList.secs[i].screen1Districts = filterDistricts;

      if (load === false ) {
        this.secList.secs[i].city = '';
        this.secList.secs[i].district = '';
        this.secList.secs[i].firm_city = '';
        this.secList.secs[i].firm_district = '';
       }
      this.validateSecEdit(i);

    }

    if ( type === 'change_sec' ) {
      this.changeSecList.secs[i].screen1Districts = filterDistricts;

       if (load === false ) {
        this.changeSecList.secs[i].city = '';
        this.changeSecList.secs[i].district = '';
       }
      this.validateChangeSecEdit(i);
      }

    if ( type === 'secFirm' ) {
      this.secFirmList.secs[i].screen1Districts = filterDistricts;

      if (load === false ) {
        this.secFirmList.secs[i].firm_city = '';
        this.secFirmList.secs[i].firm_district = '';
       }
      this.validateSecEditForSecFirm(i);

    }
    if ( type === 'change_secFirm' ) {
      this.changeSecFirmList.secs[i].screen1Districts = filterDistricts;

      if (load === false ) {
        this.changeSecFirmList.secs[i].firm_city = '';
        this.changeSecFirmList.secs[i].firm_district = '';
       }
      this.validateSecEditForSecChangeFirm(i);

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

   if ( type === 'sh_inactive' ) {
    this.shList_inactive.shs[i].screen1Districts = filterDistricts;
    if (load === false ) {
     this.shList_inactive.shs[i].city = '';
     this.shList_inactive.shs[i].district = '';
     this.shList_inactive.shs[i].firm_city = '';
     this.shList_inactive.shs[i].firm_district = '';
    }
   this.validateShareHolder_inactiveEdit(i);
 }

 if ( type === 'shFirm_inactive' ) {
   this.shFirmList_inactive.shs[i].screen1Districts = filterDistricts;
   if (load === false ) {
    this.shFirmList_inactive.shs[i].firm_city = '';
    this.shFirmList_inactive.shs[i].firm_district = '';
   }
  this.validateShareHolder_inactiveEdit(i, true);
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
     if ( type === 'director' ) {
      this.directorList.directors[i].screen1Cities = filterCities;
      if (load === false ) {
        this.directorList.directors[i].city = '';
       }
      this.validateDirectorEdit(i);
      }
      if ( type === 'change_director' ) {
        this.changeDirectorList.directors[i].screen1Cities = filterCities;
        if (load === false ) {
          this.changeDirectorList.directors[i].city = '';
         }
        this.validateChangeDirectorEdit(i);
      }

      if ( type === 'sec' ) {
        this.secList.secs[i].screen1Cities = filterCities;
        if (load === false ) {
          this.secList.secs[i].city = '';
          this.secList.secs[i].firm_city = '';
         }
        this.validateSecEdit(i);
      }

      if ( type === 'change_sec' ) {
        this.changeSecList.secs[i].screen1Cities = filterCities;
        if (load === false ) {
          this.changeSecList.secs[i].city = '';
         }
        this.validateChangeSecEdit(i);
      }

      if ( type === 'secFirm' ) {
        this.secFirmList.secs[i].screen1Cities = filterCities;
        if (load === false ) {
          this.secFirmList.secs[i].firm_city = '';
         }
        this.validateSecEditForSecFirm(i);
      }

      if ( type === 'change_secFirm' ) {
        this.changeSecFirmList.secs[i].screen1Cities = filterCities;
        if (load === false ) {
          this.changeSecFirmList.secs[i].firm_city = '';
         }
        this.validateSecEditForSecChangeFirm(i);
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
     if ( type === 'sh_inactive' ) {
      this.shList_inactive.shs[i].screen1Cities = filterCities;
      if (load === false ) {
       this.shList_inactive.shs[i].city = '';
       this.shList_inactive.shs[i].firm_city = '';
      }
     this.validateShareHolder_inactiveEdit(i);
   }

   if ( type === 'shFirm_inactive' ) {
     this.shFirmList_inactive.shs[i].screen1Cities = filterCities;
     if (load === false ) {
      this.shFirmList_inactive.shs[i].firm_city = '';
     }
    this.validateShareHolder_inactiveEdit(i , true );
  }
   }

   /**************** */

   getProvincesForBen(type) {

    // tslint:disable-next-line:prefer-const
    let provinces = Object.assign({}, this.provinces);
    // tslint:disable-next-line:prefer-const
    let filterProvince: Array<IProvince> = [];
   // tslint:disable-next-line:prefer-const
   for ( let i in provinces ) {
    filterProvince.push( provinces[i]);
    }

    if ( type === 'sec_sh_benif' ) {
       this.sec_sh_benif.screen1Provinces = filterProvince;
       this.validateSecShBenif();
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

   if ( type === 'sec_sh_benif' ) {
    this.sec_sh_benif.screen1Districts = filterDistricts;

     if (load === false ) {
      this.sec_sh_benif.city = '';
      this.sec_sh_benif.district = '';
     }

    this.validateSecShBenif();
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

    if ( type === 'sec_sh_benif' ) {

      this.sec_sh_benif.screen1Cities = filterCities;
      if (load === false ) {
        this.sec_sh_benif.city = '';
      }
      this.validateSecShBenif();
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

    if ( type === 'sec_sh_benif' ) {
       this.sec_sh_benif.screen1Provinces = filterProvince;
       this.validateSecShBenifEdit(i);
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

   if ( type === 'sec_sh_benif' ) {
    this.sec_sh_benif.screen1Districts = filterDistricts;

     if (load === false ) {
      this.sec_sh_benif.city = '';
      this.sec_sh_benif.district = '';
     }

    this.validateSecShBenifEdit(i);
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

    if ( type === 'sec_sh_benif' ) {

      this.sec_sh_benif.screen1Cities = filterCities;
      if (load === false ) {
        this.sec_sh_benif.city = '';
      }
      this.validateSecShBenifEdit(i);
    }
    if ( type === 'sh_benif') {
      this.sh_benif.screen1Cities = filterCities;
      if (load === false ) {
        this.sh_benif.city = '';
      }
      this.validateShBenifEdit(i);
    }
   }

  sanitize(url: string) {
    return this.sanitizer.bypassSecurityTrustUrl(url);
  }



  ngAfterViewInit() {

    $(document).on('click', '.record-handler-remove', function (e) {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
     // self.parent().parent().remove();
     e.stopPropagation();
    });

    $('button.add-director').on('click', function () {
      $('#director-modal .close-modal-item').trigger('click');
    });

    $('button.add-sec').on('click', function () {
      $('#sec-modal .close-modal-item').trigger('click');
    });
    $('button.add-sec-firm').on('click', function () {
      $('#sec-modal .close-modal-item').trigger('click');
    });

    $('button.add-share').on('click', function () {
      $('#share-modal .close-modal-item').trigger('click');
    });
    $('button.add-share-inactive').on('click', function () {
      $('#share-inactive-modal .close-modal-item').trigger('click');
    });

    $('button.add-share-register').on('click', function () {
      $('#share-register-modal .close-modal-item').trigger('click');
    });
    $('button.add-annual-record-row').on('click', function () {
      $('#annual-record-modal .close-modal-item').trigger('click');
    });
    $('button.add-annual-auditor-record-row').on('click', function () {
      $('#annual--auditor-record-modal .close-modal-item').trigger('click');
    });
    $('button.add-annual-charge-record-row').on('click', function () {
      $('#annual-charge-record-modal .close-modal-item').trigger('click');
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

    this.progress.progressPercentage = (this.stepOn >= 2) ? (6.25 * 2 + this.stepOn * 12.5) + '%' : (7.15 + this.stepOn * 12.5) + '%';

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
  private loadHeavyData() {
    const data = {
      companyId: this.companyId,
    };
    this.spinner.show();

    // load Company data from the server
    this.annualService.annualHeavyData(data)
      .subscribe(
        req => {

          this.provinces = req['data']['pdc']['provinces'];
          this.districts = req['data']['pdc']['districts'];
          this.cities = req['data']['pdc']['cities'];
          this.gns = req['data']['pdc']['gns'];
          this.loadData();
        }
      );

  }

  private loadData() {
    const data = {
      companyId: this.companyId,
      loginUser: this.loginUserEmail
    };
    this.spinner.show();

    // load Company data from the server
    this.annualService.annualData(data)
      .subscribe(
        req => {

          if ( req['data']['createrValid'] === false ) {

            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          // tslint:disable-next-line:radix
          this.requestId = req['data']['request_id'] ? parseInt( req['data']['request_id'] ) : 0;

          this.compayType = req['data']['companyType'];
          this.externalGlobComment = req['data']['external_global_comment'];
          this.processStatus = req['data']['processStatus'];
          this.annualReturnStatus = req['data']['annual_return_status'];

          if ( !( this.annualReturnStatus === 'OVERSEAS_ALTERATIONS_PROCESSING' || this.annualReturnStatus === 'OVERSEAS_ALTERATIONS_RESUBMIT' ) ) {
            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          this.formattedTodayValue = this.getFormatedToday();

          if (this.annualReturnStatus === 'OVERSEAS_ALTERATIONS_RESUBMIT' ) {
            this. document_confirm = true;
            this.isReasubmit = true;
          }


          this.postFixEn = req['data']['postfix'];
          this.postFixSi = req['data']['postfix_si'];
          this.postFixTa = req['data']['postfix_ta'];

        //  this.companyObjectives = Array.of(req['data']['companyObjectives']);
          this.companyAddress = req['data']['companyAddress'];
          if (req['data']['has_request_address']){
            this.requestAddress = req['data']['requestAddress'];
            this.requestAddress_change_date = req['data']['request_address_change_date'];
          }else {
            this.requestAddress =  {
              address1: '', address2: '', gn_division: '',  city: '', country: '', created_at: '', district: '', id: 0, postcode: '', province: '', updated_at: ''
            };
            this.requestAddress_change_date = '';
          }
          this.hasRequestAddress = req['data']['has_request_address'] ?  true : false;

          if (req['data']['companyForAddress']) {
            this.companyForAddress = (req['data']['companyForAddress']);
          }else {
           this.companyForAddress = {
              address1: '', address2: '', city: '', country: '', created_at: '', district: '', province: '', updated_at: '', postcode: ''
            };
          }
          if (req['data']['has_request_for_address']){
            this.requestForAddress = req['data']['requestForAddress'];
            this.requestForAddress_change_date = req['data']['request_for_address_change_date'];
          }else {
            this.requestForAddress = {
              address1: '', address2: '', city: '', country: '', created_at: '', district: '', province: '', updated_at: '', postcode: ''
            };
            this.requestForAddress_change_date = '';
          }
          this.hasRequestForAddress = req['data']['has_request_for_address'] ?  true : false;

          this.loadPDCcompany = req['data']['open_company_cdp_dropdowns'];
          this.loadForAddressCompany = req['data']['open_company_for_address'];
          this.companyRegistrationNumber =  req['data']['certificate_no'];
          this.companyInfo = req['data']['companyInfo'];
          this.loginUserInfo = req['data']['loginUser'];
          this.loginUserAddress = req['data']['loginUserAddress'];
          this.countries = req['data']['countries'];
          this.coreShareGroups = req['data']['coreShareGroups'];

          this.isGuarantyCompany =  ( this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34' );
          this.isShareholderLable = (this.isGuarantyCompany) ? 'Member' : 'Shareholder';
          let shareRegisterlabel = (this.isGuarantyCompany) ? 'Member Register' : 'Share Register';
          let Shareholderslabel = (this.isGuarantyCompany) ? 'Members' : 'Shareholders';

          this.oldCompanyInfo.oldName = req['data']['latest_name_change']['oldName'];
          this.oldCompanyInfo.old_postfix = req['data']['latest_name_change']['old_postfix'];
          this.oldCompanyInfo.old_type_id = req['data']['latest_name_change']['old_type_id'];

          this.alterOptions = req['data']['alter_options'];

          this.secList.secs = req['data']['secs'];
          this.secFirmList.secs = req['data']['secs_firms'];
          this.secs_already_exists = req['data']['secs_already_exists'];
          // tslint:disable-next-line:prefer-const
          for (let i in this.secFirmList.secs) {

            this.getProvincesForStakeHolderEdit('secFirm', i);
            this.getDistrictsForStakeholderEdit('secFirm', i , this.secFirmList.secs[i].firm_province, true );
            this.getCitiesForStakeholderEdit( 'secFirm', i , this.secFirmList.secs[i].firm_district, true  );
            this.secFirmList.secs[i].benifOwnerType = 'local';
           // this.validateSecEditForSecFirm(i);
          }

          // tslint:disable-next-line:prefer-const
          for (let i in this.secList.secs) {

            this.getProvincesForStakeHolderEdit('sec', i);
            this.getDistrictsForStakeholderEdit('sec', i , this.secList.secs[i].province, true );
            this.getCitiesForStakeholderEdit( 'sec', i , this.secList.secs[i].district, true  );
          }

          this.changeSecList.secs = req['data']['change_secs'];

          for (let i in this.changeSecList.secs) {

            this.getProvincesForStakeHolderEdit('change_sec', i);
            this.getDistrictsForStakeholderEdit('change_sec', i , this.changeSecList.secs[i].province, true );
            this.getCitiesForStakeholderEdit( 'change_sec', i , this.changeSecList.secs[i].district, true  );
          }

          this.changeSecFirmList.secs = req['data']['change_secs_firms'];
          for (let i in this.changeSecFirmList.secs) {

            this.getProvincesForStakeHolderEdit('change_secFirm', i);
            this.getDistrictsForStakeholderEdit('change_secFirm', i , this.changeSecFirmList.secs[i].firm_province, true );
            this.getCitiesForStakeholderEdit( 'change_secFirm', i , this.changeSecFirmList.secs[i].firm_district, true  );
            this.changeSecFirmList.secs[i].benifOwnerType = 'local';
           // this.validateSecEditForSecFirm(i);
          }



          this.shList.shs = req['data']['shareholders'];

          if (this.shList.shs.length) {
            // tslint:disable-next-line:prefer-const
            for (let i in this.shList.shs) {
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

          this.shFirmList.shs = req['data']['shareholderFirms'];

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
          this.sh_already_exists = req['data']['sh_already_exists'];


          this.shList_inactive.shs = req['data']['shareholders_inactive'];

          if (this.shList_inactive.shs.length) {
            // tslint:disable-next-line:prefer-const
            for (let i in this.shList_inactive.shs) {
              // tslint:disable-next-line:prefer-const
              let shareRow = this.shList_inactive.shs[i].shareRow;

              if (this.shList_inactive.shs[i].shareType === 'single') {
                this.shList_inactive.shs[i].noOfShares = shareRow.no_of_shares;
              }
              if (this.shList_inactive.shs[i].shareType === 'core') {
                this.shList_inactive.shs[i].coreGroupSelected = shareRow.sharegroupId;
              }


            }
          }

          this.shFirmList_inactive.shs = req['data']['shareholderFirms_inactive'];

          if (this.shFirmList_inactive.shs.length) {
            // tslint:disable-next-line:prefer-const
            for (let i in this.shFirmList_inactive.shs) {
              // tslint:disable-next-line:prefer-const
              let shareRow = this.shFirmList_inactive.shs[i].shareRow;

              if (this.shFirmList_inactive.shs[i].shareType === 'single') {
                this.shFirmList_inactive.shs[i].noOfShares = shareRow.no_of_shares;
              }
              if (this.shFirmList_inactive.shs[i].shareType === 'core') {
                this.shFirmList_inactive.shs[i].coreGroupSelected = shareRow.sharegroupId;
              }
              this.shFirmList_inactive.shs[i].benifOwnerType = 'local';

              this.getProvincesForStakeHolderEdit('shFirm_inactive', i);
              this.getDistrictsForStakeholderEdit('shFirm_inactive', i , this.shFirmList_inactive.shs[i].firm_province, true );
              this.getCitiesForStakeholderEdit( 'shFirm_inactive', i , this.shFirmList_inactive.shs[i].firm_district, true  );

             // this.getProvincesForBenEdit('sh_benif', i );
             // this.getDistrictsForBenEdit ('sh_benif', i , this.sh_benif.province );
              // this.getCitiesForBenEdit( 'sh_benif', i , this.sh_benif.district );

            }
          }


          // tslint:disable-next-line:prefer-const
          for (let i in this.shList_inactive.shs) {
            this.getProvincesForStakeHolderEdit('sh_inactive', i);
            this.getDistrictsForStakeholderEdit('sh_inactive', i , this.shList_inactive.shs[i].province, true );
            this.getCitiesForStakeholderEdit( 'sh_inactive', i , this.shList_inactive.shs[i].district, true  );
           // this.validateShareHolderEdit(i);
          }
          this.sh_inactive_already_exists = req['data']['sh_inactive_already_exists'];
          /************* */


          this.directorList.directors = req['data']['directors'];
          this.directors_already_exists = req['data']['directors_already_exists'];
          // tslint:disable-next-line:prefer-const
          for (let i in this.directorList.directors) {

            this.getProvincesForStakeHolderEdit('director', i);
            this.getDistrictsForStakeholderEdit('director', i , this.directorList.directors[i].province, true );
            this.getCitiesForStakeholderEdit( 'director', i , this.directorList.directors[i].district, true  );

          //  this.validateDirectorEdit(i);
          }

          this.changeDirectorList.directors = req['data']['changed_directors'];
          for (let i in this.changeDirectorList.directors) {
            this.getProvincesForStakeHolderEdit('change_director', i);
            this.getDistrictsForStakeholderEdit('change_director', i , this.changeDirectorList.directors[i].province, true );
            this.getCitiesForStakeholderEdit( 'change_director', i , this.changeDirectorList.directors[i].district, true  );
          }

          // screen1 addresses
          this.getProvincesForScreen1();
          this.getDistrictsForScreen1(this.requestAddress.province, true);
          this.getCitiesForScreen1(this.requestAddress.district, true);
          this.getGnsForScreen1(this.requestAddress.city, true);

          // new director address
          this.getProvincesForStakeHolder('director');
          this.getDistrictsForStakeholder('director', this.director.province );
          this.getCitiesForStakeholder( 'director', this.director.district );

          // new sec address
          this.getProvincesForStakeHolder('sec');
          this.getDistrictsForStakeholder('sec', this.sec.province );
          this.getCitiesForStakeholder( 'sec', this.sec.district );

          // new sh address
          this.getProvincesForStakeHolder('sh');
          this.getDistrictsForStakeholder('sh', this.sh.province );
          this.getCitiesForStakeholder( 'sh', this.sh.district );

          // new sec_sh_benif
          this.getProvincesForBen('sec_sh_benif');
          this.getDistrictsForBen('sec_sh_benif', this.sec_sh_benif.province );
          this.getCitiesForBen( 'sec_sh_benif', this.sec_sh_benif.district );

          // new sh_benif
          this.getProvincesForBen('sh_benif');
          this.getDistrictsForBen('sh_benif', this.sh_benif.province );
          this.getCitiesForBen( 'sh_benif', this.sh_benif.district );


          this.shareRegisterList.sr = req['data']['share_register'];
          this.share_register_already_exists = req['data']['share_register_already_exists'];

          // tslint:disable-next-line:prefer-const
          for (let i in this.shareRegisterList.sr) {

            this.getProvincesForShareRegisterEdit( i);
            this.getDistrictsForShareEdit( i, this.shareRegisterList.sr[i].province, true );
            this.getCitiesForShareRegisterEdit(  i , this.shareRegisterList.sr[i].district, true  );
          }

          this.annualRecordList.rec = req['data']['annual_records'];
          this.annual_records_already_exists = req['data']['annual_records_already_exists'];

          // tslint:disable-next-line:prefer-const
          for (let i in this.annualRecordList.rec) {

            this.getProvincesForAnnualRecordEdit( i);
            this.getDistrictsForAnnualRecordEdit( i, this.annualRecordList.rec[i].province, true );
            this.getCitiesForAnnualRecordEdit(  i , this.annualRecordList.rec[i].district, true  );
          }

          this.auditorList.member = req['data']['annual_auditors'];

          // tslint:disable-next-line:prefer-const
          for (let i in this.auditorList.member) {

            this.getProvincesForAnnualAuditorRecordEdit( i);
            this.getDistrictsForAnnualAuditorRecordEdit( i, this.auditorList.member[i].province, true );
            this.getCitiesForAnnualAuditorRecordEdit(  i , this.auditorList.member[i].district, true  );
          }

          this.chargeList.ch = req['data']['annual_charges'];
          this.annual_charges_already_exists = req['data']['annual_charges_already_exists'];

          // tslint:disable-next-line:prefer-const
          for (let i in this.chargeList.ch) {

            this.getProvincesForAnnualChargeRecordEdit( i);
            this.getDistrictsForAnnualChargeRecordEdit( i, this.chargeList.ch[i].province, true );
            this.getCitiesForAnnualChargeRecordEdit(  i , this.chargeList.ch[i].district, true  );
          }

          this.shareList.share = req['data']['share_records'];
          this.share_records_already_exists = req['data']['share_records_already_exists'];
          this.amount_calls_recieved =   req['data']['amount_calls_recieved'];
          this.amount_calls_unpaid =  req['data']['amount_calls_unpaid'];
          this.amount_calls_forfeited =   req['data']['amount_calls_forfeited'];
          this.amount_calls_purchased =   req['data']['amount_calls_purchased'];
          this.amount_calls_redeemed =  req['data']['amount_calls_redeemed'];
          this.resolution_date = req['data']['resolution_date'];

          this.docList = req['data']['downloadDocs'];
          this.uploadList = req['data']['uploadDocs'];
          this.allFilesUploaded = this.uploadList['uploadedAll'];
          this.otherUploadList = req['data']['otherUploadDocs'];
          this.additionalUploadList = req['data']['additionalDocs'];
          this.otherFilesUploadedAtLeast = this.additionalUploadList['uploadedAll'];

        //  this.other_docs_change_date = req['data']['other_doc_change_date'];
        this.charter_date = req['data']['charter_change_date'];
        this.statute_date = req['data']['statute_change_date'];
        this.memorandum_date = req['data']['memorandum_change_date'];
        this.article_date = req['data']['article_change_date'];

        this.charter_file_upload = this.otherUploadList['charter_file_upload'];
        this.statute_file_upload = this.otherUploadList['statute_file_upload'];
        this.memorandum_file_upload = this.otherUploadList['memorandum_file_upload'];
        this.article_file_upload = this.otherUploadList['article_file_upload'];

        this.alterType = req['data']['alterType'];

        this.court_status = req['data']['court_data']['court_status'];
          this.court_name = req['data']['court_data']['court_name'];
          this.court_date = req['data']['court_data']['court_date'];
          this.court_case_no = req['data']['court_data']['court_case_no'];
          this.court_discharged = req['data']['court_data']['court_discharged'];
          this.court_penalty = req['data']['court_data']['court_penalty'];
          this.court_period = req['data']['court_data']['court_period'];

        //  if ( !this.otherFilesUploadedAtLeast ) {
          //  this.other_docs_change_date = '';
        //  }

          this.form15_payment = (req['data']['form15_payment']) ? parseFloat( req['data']['form15_payment'] ) : 0;
          this.form45_payment = (req['data']['form45_payment_enable'] && req['data']['form45_payment']) ? parseFloat( req['data']['form45_payment'] ) : 0;
          this.form46_payment = (req['data']['form46_payment_enable'] && req['data']['form46_payment']) ? parseFloat( req['data']['form46_payment'] ) : 0;
          this.form45_payment_enable = req['data']['form45_payment_enable'];
          this.form46_payment_enable = req['data']['form46_payment_enable'];

          this.vat = (req['data']['vat']) ? parseFloat( req['data']['vat'] ) : 0;
          this.other_tax = (req['data']['other_tax']) ? parseFloat( req['data']['other_tax'] ) : 0;
          this.convinienceFee = (req['data']['convinienceFee']) ? parseFloat( req['data']['convinienceFee'] ) : 0;

          this.penalty =  parseFloat(req['data']['penalty_value']);
          let penalty_recheck = this.court_status === 'yes' ? 0 : this.penalty;

          this.total_wihtout_vat_tax = this.form15_payment + this.form45_payment + this.form46_payment + (penalty_recheck);

          this.other_taxVal = ( this.total_wihtout_vat_tax * this.other_tax ) / 100;
          this.vatVal = ( this.total_wihtout_vat_tax + this.other_taxVal ) * this.vat / 100;
          this.convinienceFeeVal = ( this.total_wihtout_vat_tax + this.other_taxVal  + this.vatVal ) * this.convinienceFee / 100;
          this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;


          this.shareholder_bulk_format = req['data']['shareholder_bulk_format'];
          this.example_shareholder_bulk_data = req['data']['example_shareholder_bulk_data'];
          this.member_bulk_format = req['data']['member_bulk_format'];
          this.example_member_bulk_data = req['data']['example_member_bulk_data'];
          this.example_ceased_shareholder_bulk_data = req['data']['example_ceased_shareholder_bulk_data'];
          this.ceased_shareholder_bulk_format = req['data']['ceased_shareholder_bulk_format'];
          this.example_ceased_member_bulk_data = req['data']['example_ceased_member_bulk_data'];
          this.ceased_member_bulk_format = req['data']['ceased_member_bulk_format'];

          this.bulkShareholderInfo = {  screen1Provinces: [], screen1Districts: [], screen1Cities: [], province: '', district: '', city: '', country: 'Sri Lanka', title: ''};
          this.getProvincesShareholderInfo();


          this.annualReturnDates.incorporation_date = req['data']['dates']['incorporation_date'];
          this.annualReturnDates.is_incorporation_date_as_last_annual_return = req['data']['dates']['is_incorporation_date_as_last_annual_return'];
          this.annualReturnDates.last_year_annual_return_date = req['data']['dates']['last_year_annual_return_date'];
          this.annualReturnDates.this_year_annual_return_date = req['data']['dates']['this_year_annual_return_date'];

          this.step1Validation();

          this.enableStep2Submission = true;
          this.enableStep2SubmissionEdit = true;

          this.validateOtherDocs('load');

          this.validateCourtOrder();

          this.changeProgressStatuses(this.stepOn);

          this.spinner.hide();
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

    this.annualService.updateCourtDetails(data)
      .subscribe(
        req => {
          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(7);

            return false;
          } else{
             alert(req['message']);
             this.court_order_valid = false;
             this.spinner.hide();
          }

        },
        error => {
          this.changeProgressStatuses(6);
          console.log(error);
          this.spinner.hide();
        }

      );

  }

  submitStep1() {

    const data = {
      companyId: this.companyId,
      companyType: this.companyInfo['type_id'],
      email: null,
      address1: this.requestAddress['address1'],
      address2: this.requestAddress['address2'],
      gn_division: this.requestAddress['gn_division'],
      city: this.requestAddress['city'],
      postcode : this.requestAddress['postcode'],
      district: this.requestAddress['district'],
      province: this.requestAddress['province'],
      forAddress1: (this.requestForAddress['address1']) ? this.requestForAddress['address1'] : '',
      forAddress2: (this.requestForAddress['address2']) ? this.requestForAddress['address2'] : '',
      forCity: (this.requestForAddress['city']) ? this.requestForAddress['city'] : '',
      forProvince: (this.requestForAddress['province']) ? this.requestForAddress['province'] : '',
      forCountry: (this.requestForAddress['country']) ? this.requestForAddress['country'] : '',
      forPostcode: (this.requestForAddress['postcode']) ? this.requestForAddress['postcode'] : '',
      oversease_alteration_address_change_date: this.requestAddress_change_date,
      oversease_alteration_for_address_change_date: this.requestForAddress_change_date
    };

    this.annualService.annualStep1Submit(data)
      .subscribe(
        req => {
          this.loadData();
          this.changeProgressStatuses(2);
        },
        error => {
          console.log(error);
        }

      );


  }
  showToggle(userType, userId = 0) {

    if (userType === 'director') {

      // tslint:disable-next-line:prefer-const
      for (let i in this.directorList.directors) {
        if (this.directorList.directors[i]['id'] === userId) {
          this.directorList.directors[i]['showEditPaneForDirector'] = this.directorList.directors[i]['showEditPaneForDirector'] === userId ? null : userId;
          return true;
        }
      }
    }

    if (userType === 'sec') {

      // tslint:disable-next-line:prefer-const
      for (let i in this.secList.secs) {

        if (this.secList.secs[i]['id'] === userId) {

          this.secList.secs[i]['showEditPaneForSec'] = this.secList.secs[i]['showEditPaneForSec'] === userId ? null : userId;
          return true;
        }
      }
    }

    if (userType === 'secFirm') {

      // tslint:disable-next-line:prefer-const
      for (let i in this.secFirmList.secs) {

        if (this.secFirmList.secs[i]['id'] === userId) {

          this.secFirmList.secs[i]['showEditPaneForSec'] = this.secFirmList.secs[i]['showEditPaneForSec'] === userId ? null : userId;
          return true;
        }
      }
    }

    if (userType === 'shFirm') {

      // tslint:disable-next-line:prefer-const
      for (let i in this.shFirmList.shs) {

        if (this.shFirmList.shs[i]['id'] === userId) {

          this.shFirmList.shs[i]['showEditPaneForSh'] = this.shFirmList.shs[i]['showEditPaneForSh'] === userId ? null : userId;
          return true;
        }
      }
    }

    if (userType === 'shFirm_inactive') {

      // tslint:disable-next-line:prefer-const
      for (let i in this.shFirmList_inactive.shs) {

        if (this.shFirmList_inactive.shs[i]['id'] === userId) {

          this.shFirmList_inactive.shs[i]['showEditPaneForSh'] = this.shFirmList_inactive.shs[i]['showEditPaneForSh'] === userId ? null : userId;
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

    if (userType === 'sh_inactive') {

      // tslint:disable-next-line:prefer-const
      for (let i in this.shList_inactive.shs) {

        if (this.shList_inactive.shs[i]['id'] === userId) {
          this.shList_inactive.shs[i]['showEditPaneForSh'] = this.shList_inactive.shs[i]['showEditPaneForSh'] === userId ? null : userId;
          return true;
        }
      }
    }

  }


  showToggleForChangedStakeholders(userType, userId = 0) {

    if (userType === 'director') {

      // tslint:disable-next-line:prefer-const
      for (let i in this.changeDirectorList.directors) {
        if (this.changeDirectorList.directors[i]['id'] === userId) {
          this.changeDirectorList.directors[i]['showEditPaneForDirector'] = this.changeDirectorList.directors[i]['showEditPaneForDirector'] === userId ? null : userId;
          return true;
        }
      }
    }
    if (userType === 'sec') {

      // tslint:disable-next-line:prefer-const
      for (let i in this.changeSecList.secs) {

        if (this.changeSecList.secs[i]['id'] === userId) {

          this.changeSecList.secs[i]['showEditPaneForSec'] = this.changeSecList.secs[i]['showEditPaneForSec'] === userId ? null : userId;
          return true;
        }
      }
    }

    if (userType === 'secFirm') {

      // tslint:disable-next-line:prefer-const
      for (let i in this.changeSecFirmList.secs) {

        if (this.changeSecFirmList.secs[i]['id'] === userId) {

          this.changeSecFirmList.secs[i]['showEditPaneForSec'] = this.changeSecFirmList.secs[i]['showEditPaneForSec'] === userId ? null : userId;
          return true;
        }
      }
    }


  }

  selectStakeHolderType(stakeholder, type) {

    this.loadNICstakeholders = false;
    this.openAddressPart = false;

    if (stakeholder === 'director') {
      this.director = { id: 0, showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '' , screen1Districts: [], screen1Cities: [], passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec: true};
      this.director.type = type;

      this.directorAlreadyExistMessage = '';

      if (this.director.type !== 'local') {
        this.directorNicLoaded = true;
        this.getProvincesForStakeHolder('director');
        this.director.nic = '';
      } else {
        this.directorNicLoaded = false;
        this.getProvincesForStakeHolder('director');

      }
      this.validateDirector();

    } else if (stakeholder === 'sec') {
      // tslint:disable-next-line:max-line-length
      this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', benifOwnerType : 'local', screen1Districts: [], screen1Cities: [], validateSecShBenifInEdit : false, secBenifList : { ben : [] } , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '', firm_date: '' };
      this.sec.type = type;

      this.secAlreadyExistMessage = '';

      if (this.sec.type !== 'local') {
        this.secNicLoaded = true;
        this.getProvincesForStakeHolder('sec');
      } else {
        this.getProvincesForStakeHolder('sec');
        this.secNicLoaded = false;
      }

      this.validateSec();

    } else if (stakeholder === 'sh') {
      // tslint:disable-next-line:max-line-length
      this.sh = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', screen1Districts: [], screen1Cities: [], noOfShares: 0, shareholderType: 'natural', benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '' , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: '' };
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
    }else if (stakeholder === 'sh_inactive') {
      // tslint:disable-next-line:max-line-length
      this.sh_inactive = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', screen1Districts: [], screen1Cities: [], noOfShares: 0, shareholderType: 'natural', benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '' , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: '' };
      this.sh_inactive.type = type;

      this.shAlreadyExistMessage = '';
      if (this.sh_inactive.type !== 'local') {
        this.getProvincesForStakeHolder('sh_inactive');
        this.shNicLoaded = true;
      } else {
        this.getProvincesForStakeHolder('sh_inactive');
        this.shNicLoaded = false;
      }
      this.validateShareHolder_inactive();
    }
  }
  changeDefaultStatus() {

    // tslint:disable-next-line:max-line-length
    this.director = { id: 0, secRegDate: '', showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec : true };
    // tslint:disable-next-line:max-line-length
    this.sec  = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', benifOwnerType : 'local', screen1Provinces: [], screen1Districts: [], screen1Cities: [], firm_city: '' , firm_district: '', firm_province: '', validateSecShBenifInEdit : false, secBenifList : { ben : [] } };
    // tslint:disable-next-line:max-line-length
    this.sh = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , firm_city: '', firm_district: '', firm_province: '',  benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '' , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: '' };
    // tslint:disable-next-line:max-line-length
    this.sh_inactive = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , firm_city: '', firm_district: '', firm_province: '',  benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '' , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: '' };
    this.sh_benif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] };
    this.sec_sh_benif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '',  screen1Provinces: [], screen1Districts: [], screen1Cities: [] };

    this.director.type = 'local';
    this.sec.type = 'local';
    this.sh.type = 'local';
    this.sh_inactive.type = 'local';
    this.loadNICstakeholders = false;
    this.directorNicLoaded = false;
    this.openAddressPart = false;
    this.secNicLoaded = false;
    this.shNicLoaded = false;

    this.getProvincesForStakeHolder('director');
    this.getProvincesForStakeHolder('sec');
    this.getProvincesForStakeHolder('sh');
    this.getProvincesForStakeHolder('sh_inactive');

    this.getProvincesForBen('sec_sh_benif');

    this.getProvincesForBen('sh_benif');

    this.guarantee_sec_err_happend = false;

  }
  secTypeCheck() {

    this.sec.isShareholder = false;
    this.sec.shareType = 'single';
    this.sec.coreGroupSelected = null;
    this.sec.coreShareGroupName = '';
    this.sec.coreShareValue = null;
    this.sec.nic = '';
    this.sec.firstname = ''; this.sec.lastname = ''; this.sec.email = '', this.sec.phone = '', this.sec.mobile = '';
    this.sec.province = ''; this.sec.district = ''; this.sec.city = ''; this.sec.localAddress1 = '', this.sec.localAddress2 = ''; this.sec.postcode = '';
    this.sec.pvNumber = '', this.sec.firm_name = '', this.sec.firm_province = '', this.sec.firm_district = '', this.sec.firm_localAddress1 = '', this.sec.firm_localAddress2 = '', this.sec.firm_email = '', this.sec.firm_postcode = '';
    this.sec.firm_mobile = '', this.sec.firm_phone = '';
    this.guarantee_sec_err_happend = false;

    this.secNicLoaded = false;



  }
  validateRegCheck($e) {

    this.validateSec();
    this.sec.isReg = $e ? true : false;
    this.validateSec();
    this.validateSecForiegn();
  }
  validateRegCheckEdit($e, rowId) {
    // tslint:disable-next-line:prefer-const
    let secRow = this.secList.secs[rowId];

    secRow.isReg = $e ? true : false;
    this.validateSecEdit(rowId);

  }

  validateRegCheckEditForChangeSec($e, rowId) {
    // tslint:disable-next-line:prefer-const
    let secRow = this.changeSecList.secs[rowId];

    secRow.isReg = $e ? true : false;
    this.validateChangeSecEdit(rowId);

  }
  validateOppDate(type = 'add', stakeholder = 'director', rowId = 0) {

          if (stakeholder === 'change-director') {
            this.changeDirectorList.directors[rowId].changedate = '';
            this.validateChangeDirectorEdit(rowId);
          }

          if (stakeholder === 'change-sec') {
            this.changeSecList.secs[rowId].changedate = '';
            this.validateChangeSecEdit(rowId);
          }

          if (stakeholder === 'change-secFirm') {
            this.changeSecFirmList.secs[rowId].changedate = '';
            this.validateSecEditForSecChangeFirm(rowId);
          }
    }

    resetShRecord() {
      // func
    }
    resetShInactiveRecord(){
      // func
    }
    resetDirRecord() {
      // func
    }
    resetSecRecord() {
      // func
    }


  saveDirectorRecord() {

    if (this.director.type === 'local') {
      this.director.country = 'Sri Lanka';
    }
    // tslint:disable-next-line:prefer-const
    let copy = Object.assign({}, this.director);

    this.removeDuplicatesByNIC(1);

    this.directorList.directors.push(copy);

    this.directorNicLoaded = false;
    this.secNicLoaded = false;
    this.shNicLoaded = false;

    // tslint:disable-next-line:max-line-length
    this.director = { id: 0, secRegDate: '', showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', screen1Districts: [], screen1Cities: [] , passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec: true };
    this.validDirector = false;
    this.submitDirectors('remove');

  }

  submitDirectors(action = '') {

    this.loadData();

    if ( action === 'remove' ) {
      this.changeProgressStatuses(2);
    } else {
      this.changeProgressStatuses(3);
    }


  }

  submitSecretorySection(action = '') {

    this.loadData();
    if ( action === 'remove' ) {
      this.changeProgressStatuses(3);
    } else {
      this.changeProgressStatuses(4);
    }


  }



  saveSecRecord() {

    if (this.sec.type === 'local') {
      this.sec.country = 'Sri Lanka';
    }
    // tslint:disable-next-line:prefer-const
    let copy1 = Object.assign({}, this.sec);

    if ( this.sec.secType === 'firm' ) {
      copy1.secBenifList = this.secBenifList;
    }

    // this.removeDuplicatesByNIC(2); // remove nic duplicates
    this.secList.secs.push(copy1);

    this.directorNicLoaded = false;
    this.secNicLoaded = false;
    this.shNicLoaded = false;

    // tslint:disable-next-line:max-line-length
    this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '', firm_date : '' };
    this.validSec = false;
    this.submitSecretories('remove');

  }

  submitSecretories(action = '') {

    // tslint:disable-next-line:prefer-const
    let copySecList = Object.assign({}, this.secList);
    if (this.secFirmList.secs.length) {
      // tslint:disable-next-line:prefer-const
      for (let i in this.secFirmList.secs) {

        // tslint:disable-next-line:prefer-const
        let formRecord: ISecretory = {
          id: this.secFirmList.secs[i].id,
          type:  this.secFirmList.secs[i].type,
          title: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].title : '',
          firstname: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].firstname : '',
          lastname: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].lastname : '',
          province: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].province : '',
          district: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].district : '',
          city: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].city : '',
          phone: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].phone : '',
          email: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].email : '',
          mobile: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].mobile : '',
          regDate: '', isReg: false,
          date: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].date : '',
          occupation: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].occupation : '',
          showEditPaneForSec: 0,
          localAddress1: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].localAddress1 : '',
          localAddress2: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].localAddress2 : '',
          postcode: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].postcode : '',
          nic: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].nic : '',
          passport: '', country: '', share: 0,
          pvNumber: this.secFirmList.secs[i].pvNumber,
          firm_name: this.secFirmList.secs[i].firm_name,
          firm_province: this.secFirmList.secs[i].firm_province,
          firm_district: this.secFirmList.secs[i].firm_district,
          firm_city: this.secFirmList.secs[i].firm_city,
          firm_localAddress1: this.secFirmList.secs[i].firm_localAddress1,
          firm_localAddress2: this.secFirmList.secs[i].firm_localAddress2,
          firm_postcode: this.secFirmList.secs[i].firm_postcode,
          firm_country:  this.secFirmList.secs[i].firm_country,
          firm_email: this.secFirmList.secs[i].firm_email,
          firm_phone: this.secFirmList.secs[i].firm_phone,
          firm_mobile: this.secFirmList.secs[i].firm_mobile,
          firm_date: this.secFirmList.secs[i].firm_date,
          secType: 'firm',
          isShareholderEdit: this.secFirmList.secs[i].isShareholderEdit,
          shareTypeEdit: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].shareTypeEdit : null,
          noOfSingleSharesEdit: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].noOfSingleSharesEdit : null,
          coreGroupSelected: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].coreGroupSelected : null,
          coreShareGroupNameEdit: this.secFirmList.secs[i].coreShareGroupNameEdit ? this.secFirmList.secs[i].coreShareGroupNameEdit : null,
          coreShareValueEdit: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].coreShareValueEdit : null,
          secBenifList:  this.secFirmList.secs[i].secBenifList,
          forAddress1: '',
          forAddress2: '',
          forCity: '',
          forProvince: '',
          passport_issued_country: '',
        };
        copySecList.secs.push(formRecord);
      }
    }

    const data = {
      companyId: this.companyId,
      loginUser: this.loginUserEmail,
      secretories: copySecList,
    };

    this.annualService.annualSecretoriesSubmit(data)
      .subscribe(
        req => {
          this.loadData();
          if (action === 'remove') { // in case of removing stakeholder, keep the position on same page.
            this.changeProgressStatuses((this.isGuarantyCompany ? 4 : 5));
            return false;
          }

          this.changeProgressStatuses((this.isGuarantyCompany ? 5 : 6));
        },
        error => {
          this.changeProgressStatuses((this.isGuarantyCompany ? 4 : 5));
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
    this.sh = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural',  screen1Districts: [], screen1Cities: [], benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '' , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: ''};

    this.validSh = false;
    this.submitShareholders('remove');
    this.benifList.ben = [];
  }

  saveShareHolderInactiveRecord() {

    if (this.sh_inactive['type'] === 'local') {
      this.sh_inactive.country = 'Sri Lanka';
    }
    // this.sh.benifiList = this.benifList;


    // tslint:disable-next-line:prefer-const
    let copy3 = Object.assign({}, this.sh_inactive);
    this.shList_inactive.shs.push(copy3);

    // tslint:disable-next-line:max-line-length
    this.sh_inactive = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural',  screen1Districts: [], screen1Cities: [], benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '' , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: ''};

    this.validSh = false;
    this.submitShareholders_inactive('remove');
    this.benifList.ben = [];
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
      this.annualService.annualShareholdersSubmit(data)
        .subscribe(
          req => {
            this.loadData();
            if (action === 'remove') { // in case of removing stakeholder, keep the position on same page.
              this.changeProgressStatuses((this.isGuarantyCompany ? 6 : 7));
              return false;
            }
            this.changeProgressStatuses((this.isGuarantyCompany ? 7 : 8));
          },
          error => {
            this.changeProgressStatuses((this.isGuarantyCompany ? 6 : 7));
            console.log(error);
          }
      );

  }
  submitShareholders_inactive( action = '' ) {

    // tslint:disable-next-line:prefer-const
    let copyShList = Object.assign({}, this.shList_inactive);
    if (this.shFirmList_inactive.shs.length) {
      // tslint:disable-next-line:prefer-const
      for (let i in this.shFirmList_inactive.shs) {
        // tslint:disable-next-line:prefer-const
        let formRecord: IShareHolder = {
          id: this.shFirmList_inactive.shs[i].id,
          type: this.shFirmList_inactive.shs[i].type,
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
          pvNumber: this.shFirmList_inactive.shs[i].pvNumber,
          firm_name: this.shFirmList_inactive.shs[i].firm_name,
          firm_province: this.shFirmList_inactive.shs[i].firm_province,
          firm_district: this.shFirmList_inactive.shs[i].firm_district,
          firm_city: this.shFirmList_inactive.shs[i].firm_city,
          firm_localAddress1: this.shFirmList_inactive.shs[i].firm_localAddress1,
          firm_localAddress2: this.shFirmList_inactive.shs[i].firm_localAddress2,
          firm_postcode: this.shFirmList_inactive.shs[i].firm_postcode,
          firm_email: this.shFirmList_inactive.shs[i].firm_email,
          firm_phone: this.shFirmList_inactive.shs[i].firm_phone,
          firm_mobile: this.shFirmList_inactive.shs[i].firm_mobile,
          firm_date: this.shFirmList_inactive.shs[i].firm_date,
          shareholderType: this.shFirmList_inactive.shs[i].shareholderType,
          shareType: this.shFirmList_inactive.shs[i].shareType,
          noOfShares: this.shFirmList_inactive.shs[i].noOfShares,
          coreGroupSelected: this.shFirmList_inactive.shs[i].coreGroupSelected ? this.shFirmList_inactive.shs[i].coreGroupSelected : null,
          coreShareGroupName: this.shFirmList_inactive.shs[i].coreShareGroupName ? this.shFirmList_inactive.shs[i].coreShareGroupName : '',
          noOfSharesGroup:  this.shFirmList_inactive.shs[i].noOfSharesGroup ? this.shFirmList_inactive.shs[i].noOfSharesGroup : null,
          showEditPaneForSh: this.shFirmList_inactive.shs[i].showEditPaneForSh,
          benifiList: this.shFirmList_inactive.shs[i].benifiList
        };
        copyShList.shs.push(formRecord);
      }
     }

     const data = {
       companyId: this.companyId,
       loginUser: this.loginUserEmail,
       shareholders: copyShList,
       set_operation: 'inactive'
     };
     this.annualService.annualShareholdersSubmit(data)
       .subscribe(
         req => {
           this.loadData();
           if (action === 'remove') { // in case of removing stakeholder, keep the position on same page.
             this.changeProgressStatuses((this.isGuarantyCompany ? 7 : 8));
             return false;
           }
           this.changeProgressStatuses((this.isGuarantyCompany ? 8 : 9));
         },
         error => {
           this.changeProgressStatuses((this.isGuarantyCompany ? 7 : 8));
           console.log(error);
         }
     );

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

  removeShInactiveRecord(i: number, userId: number = 0) {

    if ( !confirm('Are you sure you want to remove this stakeholder?') ) {
      return true;
    }

    this.shList_inactive.shs.splice(i, 1);
    if (!userId) {
      return true;
    }
    this.submitShareholders_inactive('remove');

  }

  removeShFirmRecord(i: number, userId: number = 0) {
    if ( !confirm('Are you sure you want to remove this stakeholder?') ) {
      return true;
    }

    this.shFirmList.shs.splice(i, 1);
    if (!userId) {
      return true;
    }
    this.submitShareholders('remove');
  }
  removeShFirmInactiveRecord(i: number, userId: number = 0) {
    if ( !confirm('Are you sure you want to remove this stakeholder?') ) {
      return true;
    }

    this.shFirmList_inactive.shs.splice(i, 1);
    if (!userId) {
      return true;
    }
    this.submitShareholders_inactive('remove');
  }


  /********validation functions *****/

  validateNewAddress() {

    if (
       this.requestAddress.address1  ||
       this.requestAddress.address2 ||
       this.requestAddress.city ||
       this.requestAddress.district ||
       this.requestAddress.province ||
       this.requestAddress.postcode
     ) {
       return (
         this.requestAddress.address1 &&
         this.requestAddress.city &&
         this.requestAddress.district &&
         this.requestAddress.city &&
         this.requestAddress.province &&
         this.requestAddress.postcode &&
         this.requestAddress_change_date
       );
     } else {
       return true;
     }

  }

  validateNewForAddress() {

    if (
       this.requestForAddress.address1  ||
       this.requestForAddress.address2 ||
       this.requestForAddress.city ||
       this.requestForAddress.province ||
       this.requestForAddress.postcode ||
       this.requestForAddress.country
     ) {
       return (
         this.requestForAddress.address1 &&
         this.requestForAddress.address2 &&
         this.requestForAddress.city &&
         this.requestForAddress.city &&
         this.requestForAddress.province &&
         this.requestForAddress.postcode &&
         this.requestForAddress_change_date &&
         this.requestForAddress.country
       );
     } else {
       return true;
     }

  }

  step1Validation() {


    if (
      this.validateNewAddress() && this.validateNewForAddress()
    ) {
      this.enableStep1Submission = true;
    } else {
      this.enableStep1Submission = false;
    }

  }

  validateDirector() {

    if (this.director.type === 'local') {

      if (!
        (
          this.director.nic && this.validateNIC(this.director.nic) &&
          !this.isDirectorAlreadyExist('local') &&
          this.director.title &&
          this.director.email && this.validateEmail(this.director.email) &&
          this.director.firstname &&
          this.director.lastname &&
          this.director.province &&
          this.director.district &&
          this.director.city &&
          this.director.mobile && this.phonenumber(this.director.mobile, this.director.type) &&
          this.director.localAddress1 &&
          this.director.postcode &&
          this.director.date &&
          (( (this.compayType.key === 'COMPANY_TYPE_PUBLIC' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34') && this.director.isSec ) ? this.director.secRegDate : true ) &&

          ((this.director.isShareholder === undefined || this.director.isShareholder === false) || this.director.shareType === 'single' && this.director.noOfSingleShares ||
            this.director.shareType === 'core' && this.director.coreGroupSelected ||
            this.director.shareType === 'core' && (this.director.coreShareGroupName && this.director.coreShareValue)

          )

        )

      ) {

        this.directorValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.validDirector = false;

        return false;
      } else {

        this.directorValitionMessage = '';
        this.validDirector = true;
        return true;

      }

    }

    if (this.director.type === 'foreign') {

      if (!(this.director.passport && this.director.passport_issued_country &&
        !this.isDirectorAlreadyExist('foreign') &&
        this.director.title &&
        this.director.email && this.validateEmail(this.director.email) &&
        this.director.firstname &&
        this.director.lastname &&

        ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ?
         true : ( this.director.province && this.director.district && this.director.city && this.director.localAddress1 &&  this.director.postcode )) &&


        this.director.forProvince &&
        this.director.forCity &&
        this.director.country &&
        this.director.mobile && this.phonenumber(this.director.mobile, this.director.type) &&
        this.director.forAddress1 &&
        this.director.forPostcode &&
        ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS')  ? true :  this.director.forPostcode ) &&
        this.director.date &&
        ((this.director.isShareholder === undefined || this.director.isShareholder === false) || this.director.shareType === 'single' && this.director.noOfSingleShares ||
          this.director.shareType === 'core' && this.director.coreGroupSelected ||
          this.director.shareType === 'core' && (this.director.coreShareGroupName && this.director.coreShareValue)

        )
      )) {

        this.directorValitionMessage = 'Please fill all required fields denoted by asterik(*)';
        this.validDirector = false;
        return false;

      } else {
        this.directorValitionMessage = '';
        this.validDirector = true;
        return true;

      }

    }

  }
  validateChangeDirectorEdit(rowId) {

    // tslint:disable-next-line:prefer-const
    let directorRow = this.changeDirectorList.directors[rowId];
    if (directorRow.type === 'local') {

      if (!(directorRow.nic && this.validateNIC(directorRow.nic) &&
        // this.director.title &&
        directorRow.email && this.validateEmail(directorRow.email) &&
        directorRow.firstname &&
        directorRow.lastname &&
        directorRow.province &&
        directorRow.district &&
        directorRow.city &&
        directorRow.mobile && this.phonenumber(directorRow.mobile, directorRow.type) &&
        directorRow.localAddress1 &&
        directorRow.postcode &&
        directorRow.date &&
        (((this.compayType.key === 'COMPANY_TYPE_PUBLIC' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34') && directorRow.isSecEdit ) ? directorRow.secRegDate : true ) &&
        ((directorRow.isShareholderEdit === undefined || directorRow.isShareholderEdit === false) || directorRow.shareTypeEdit === 'single' && directorRow.noOfSingleSharesEdit ||
          directorRow.shareTypeEdit === 'core' && directorRow.coreGroupSelectedEdit ||
          directorRow.shareTypeEdit === 'core' && (directorRow.coreShareGroupNameEdit && directorRow.coreShareValueEdit)

        )
      )
      ) {
          this.directorValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
          this.enableStep2Submission = false;
          this.enableStep2SubmissionEdit = false;
         return false;
      } else {

         this.directorValitionMessage = '';
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
        directorRow.forPostcode &&
        directorRow.mobile && this.phonenumber(directorRow.mobile, directorRow.type) &&
        directorRow.forAddress1 &&
        ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ?
         true : ( directorRow.province && directorRow.district && directorRow.city && directorRow.localAddress1 &&  directorRow.postcode )) &&
       // directorRow.province && directorRow.district && directorRow.city && directorRow.localAddress1 &&  directorRow.postcode &&
        directorRow.country &&
        ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS')  ? true :  directorRow.forPostcode ) &&
        directorRow.date &&
        ((directorRow.isShareholderEdit === undefined || directorRow.isShareholderEdit === false) || directorRow.shareTypeEdit === 'single' && directorRow.noOfSingleSharesEdit ||
          directorRow.shareTypeEdit === 'core' && directorRow.coreGroupSelectedEdit ||
          directorRow.shareTypeEdit === 'core' && (directorRow.coreShareGroupNameEdit && directorRow.coreShareValueEdit)

        )
      )) {

         this.directorValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.enableStep2Submission = false;
        this.enableStep2SubmissionEdit = false;
        return false;

      } else {
         this.directorValitionMessage = '';
        this.enableStep2Submission = true;
        this.enableStep2SubmissionEdit = true;
        return true;
      }
    }

  }


  validateDirectorEdit(rowId) {

    if (!this.directorList.directors.length){
        this.directorValitionMessage = '';
         this.enableStep2Submission = true;
        this.enableStep2SubmissionEdit = true;
        return true;
    }

    // tslint:disable-next-line:prefer-const
    let directorRow = this.directorList.directors[rowId];
    if (directorRow.type === 'local') {

      if (!(directorRow.nic && this.validateNIC(directorRow.nic) &&
        // this.director.title &&
        directorRow.email && this.validateEmail(directorRow.email) &&
        directorRow.firstname &&
        directorRow.lastname &&
        directorRow.province &&
        directorRow.district &&
        directorRow.city &&
        directorRow.mobile && this.phonenumber(directorRow.mobile, directorRow.type) &&
        directorRow.localAddress1 &&
        directorRow.postcode &&
        directorRow.date &&
        directorRow.changedate &&
        (((this.compayType.key === 'COMPANY_TYPE_PUBLIC' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34') && directorRow.isSecEdit ) ? directorRow.secRegDate : true ) &&
        ((directorRow.isShareholderEdit === undefined || directorRow.isShareholderEdit === false) || directorRow.shareTypeEdit === 'single' && directorRow.noOfSingleSharesEdit ||
          directorRow.shareTypeEdit === 'core' && directorRow.coreGroupSelectedEdit ||
          directorRow.shareTypeEdit === 'core' && (directorRow.coreShareGroupNameEdit && directorRow.coreShareValueEdit)

        )
      )
      ) {
          this.directorValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
          this.enableStep2Submission = false;
          this.enableStep2SubmissionEdit = false;
         return false;
      } else {

         this.directorValitionMessage = '';
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
        directorRow.forPostcode &&
        directorRow.mobile && this.phonenumber(directorRow.mobile, directorRow.type) &&
        directorRow.forAddress1 &&
        ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ?
         true : ( directorRow.province && directorRow.district && directorRow.city && directorRow.localAddress1 &&  directorRow.postcode )) &&
       // directorRow.province && directorRow.district && directorRow.city && directorRow.localAddress1 &&  directorRow.postcode &&
        directorRow.country &&
        ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS')  ? true :  directorRow.forPostcode ) &&
        directorRow.date &&
        directorRow.changedate &&
        ((directorRow.isShareholderEdit === undefined || directorRow.isShareholderEdit === false) || directorRow.shareTypeEdit === 'single' && directorRow.noOfSingleSharesEdit ||
          directorRow.shareTypeEdit === 'core' && directorRow.coreGroupSelectedEdit ||
          directorRow.shareTypeEdit === 'core' && (directorRow.coreShareGroupNameEdit && directorRow.coreShareValueEdit)

        )
      )) {

         this.directorValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.enableStep2Submission = false;
        this.enableStep2SubmissionEdit = false;
        return false;

      } else {
         this.directorValitionMessage = '';
        this.enableStep2Submission = true;
        this.enableStep2SubmissionEdit = true;
        return true;
      }
    }

  }
  validateSec() {
    if (!(

      (
        (this.sec.secType === 'firm') ?

          (
            ((this.sec.secType === 'firm' && this.compayType.value === 'Public') ? this.sec.pvNumber : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_name : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_date : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_province : true) &&
             ((this.sec.secType === 'firm' && !(this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ) ? this.sec.firm_district : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_city : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_localAddress1 : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_postcode : true) &&
            ((this.sec.secType === 'firm') ? (this.sec.firm_mobile && this.phonenumber(this.sec.firm_mobile, this.sec.type)) : true) &&
            ((this.sec.secType === 'firm') ? (this.sec.firm_email && this.validateEmail(this.sec.firm_email)) : true) &&
            ((this.sec.secType === 'firm' && this.sec.type !== 'local' &&  (this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ) ? this.sec.firm_country : true )
          ) :


          (this.sec.nic && this.validateNIC(this.sec.nic) &&
            !this.isSecAlreadyExist() &&
            this.sec.title &&
            this.sec.firstname &&
            this.sec.lastname &&
            this.sec.province &&
            this.sec.district &&
            this.sec.city &&
            this.sec.postcode &&
            this.sec.date &&
            this.sec.mobile && this.phonenumber(this.sec.mobile) &&
            this.sec.email && this.validateEmail(this.sec.email) &&
            this.sec.localAddress1 &&
            ( (this.compayType.key === 'COMPANY_TYPE_PUBLIC' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34' ) ?  this.sec.regDate : true) )
      ) &&

      ((this.sec.isShareholder === undefined || this.sec.isShareholder === false) || this.sec.shareType === 'single' && this.sec.noOfSingleShares ||
        this.sec.shareType === 'core' && this.sec.coreGroupSelected ||
        this.sec.shareType === 'core' && (this.sec.coreShareGroupName && this.sec.coreShareValue)

      )
     // && ((this.sec.secType === 'firm' && this.sec.isShareholder) ? this.secBenifList.ben.length  : true )

    )


    ) {

      this.secValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validSec = false;

      return false;
    } else {

      if (this.sec.isReg) {

        if (!this.sec.regDate) {

          this.secValitionMessage = 'Please add the registration Number';
          this.validSec = false;
          return false;

        } else {

          this.secValitionMessage = '';
          this.validSec = true;
          return true;

        }
      } else {
        this.secValitionMessage = '';
        this.validSec = true;
        return true;
      }


    }




  }
  validateSecEdit(rowId) {
    // tslint:disable-next-line:prefer-const
    let secRow = this.secList.secs[rowId];
    if (!(

      ((secRow.secType === 'firm' && this.compayType.value === 'Public') ? secRow.pvNumber : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_name : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_province : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_district : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_city : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_localAddress1 : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_postcode : true) &&


     ( (secRow.type === 'local') ?  secRow.nic && this.validateNIC(secRow.nic) : (secRow.passport && secRow.passport_issued_country) ) &&
      secRow.date &&
      secRow.changedate &&
      secRow.firstname &&
      secRow.lastname &&
      secRow.province &&
      secRow.district &&
      secRow.city  &&
      secRow.postcode &&
      ( secRow.type === 'foreign' ? secRow.forProvince : true ) &&
      ( secRow.type === 'foreign' ? secRow.forCity : true ) &&
      ( secRow.type === 'foreign' ? ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS')  ? true :   secRow.forPostcode ) : true ) &&
      secRow.mobile && this.phonenumber(secRow.mobile, secRow.type ) &&
      secRow.email && this.validateEmail(secRow.email) &&
      secRow.localAddress1 &&
      ( secRow.type === 'foreign' ? secRow.forAddress1 : true ) &&
      ( (this.compayType.key === 'COMPANY_TYPE_PUBLIC' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34' ) ?  secRow.regDate : true) &&
      ((secRow.isShareholderEdit === undefined || secRow.isShareholderEdit === false) || secRow.shareTypeEdit === 'single' && secRow.noOfSingleSharesEdit ||
        secRow.shareTypeEdit === 'core' && secRow.coreGroupSelectedEdit ||
        secRow.shareTypeEdit === 'core' && (secRow.coreShareGroupNameEdit && secRow.coreShareValueEdit)

      )


    )) {

        this.enableStep2Submission = false;
        this.enableStep2SubmissionEdit = false;
      return false;
    } else {

      if (secRow.isReg) {

        if (!secRow.regDate) {

          this.enableStep2Submission = false;
          this.enableStep2SubmissionEdit = false;
          return false;

        } else {

          this.enableStep2Submission = true;
          this.enableStep2SubmissionEdit = true;
          return true;

        }
      } else {
        this.enableStep2Submission = true;
        this.enableStep2SubmissionEdit = true;
        return true;
      }


    }
  }

  validateChangeSecEdit(rowId) {
    // tslint:disable-next-line:prefer-const
    let secRow = this.changeSecList.secs[rowId];
    if (!(

      ((secRow.secType === 'firm' && this.compayType.value === 'Public') ? secRow.pvNumber : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_name : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_province : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_district : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_city : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_localAddress1 : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_postcode : true) &&


     ( (secRow.type === 'local') ?  secRow.nic && this.validateNIC(secRow.nic) : (secRow.passport && secRow.passport_issued_country) ) &&
      secRow.date &&
      secRow.firstname &&
      secRow.lastname &&
      secRow.province &&
      secRow.district &&
      secRow.city &&
      secRow.postcode &&
      ( secRow.type === 'foreign' ? secRow.forProvince : true ) &&
      ( secRow.type === 'foreign' ? secRow.forCity : true ) &&
      ( secRow.type === 'foreign' ? ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS')  ? true :   secRow.forPostcode ) : true ) &&
      secRow.mobile && this.phonenumber(secRow.mobile, secRow.type ) &&
      secRow.email && this.validateEmail(secRow.email) &&
      secRow.localAddress1 &&
      ( secRow.type === 'foreign' ? secRow.forAddress1 : true ) &&
      ( (this.compayType.key === 'COMPANY_TYPE_PUBLIC' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34' ) ?  secRow.regDate : true) &&
      ((secRow.isShareholderEdit === undefined || secRow.isShareholderEdit === false) || secRow.shareTypeEdit === 'single' && secRow.noOfSingleSharesEdit ||
        secRow.shareTypeEdit === 'core' && secRow.coreGroupSelectedEdit ||
        secRow.shareTypeEdit === 'core' && (secRow.coreShareGroupNameEdit && secRow.coreShareValueEdit)

      )


    )) {

        this.enableStep2Submission = false;
        this.enableStep2SubmissionEdit = false;
      return false;
    } else {

      if (secRow.isReg) {

        if (!secRow.regDate) {

          this.enableStep2Submission = false;
          this.enableStep2SubmissionEdit = false;
          return false;

        } else {

          this.enableStep2Submission = true;
          this.enableStep2SubmissionEdit = true;
          return true;

        }
      } else {
        this.enableStep2Submission = true;
        this.enableStep2SubmissionEdit = true;
        return true;
      }


    }
  }





  validateSecEditForSecFirm(rowId) {
    // tslint:disable-next-line:prefer-const
    let secRow = this.secFirmList.secs[rowId];


    if (!(

      ((this.compayType.value === 'Public') ? secRow.pvNumber : true) &&
      secRow.firm_name &&
      secRow.firm_province &&
     // secRow.firm_district &&
      ((secRow.secType === 'firm' && !(this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ) ? secRow.firm_district : true) &&
      secRow.firm_city &&
      secRow.firm_localAddress1 &&
      secRow.firm_postcode &&
      secRow.firm_date &&
      secRow.changedate &&
      (secRow.firm_mobile && this.phonenumber(secRow.firm_mobile, secRow.type)) &&
      (secRow.firm_email && this.validateEmail(secRow.firm_email) &&
      ((secRow.secType === 'firm' && secRow.type !== 'local' &&  (this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ) ? secRow.firm_country : true )

        &&
        (secRow.isShareholderEdit) ?
        (
          (secRow.shareTypeEdit === 'single' && secRow.noOfSingleSharesEdit ||
            secRow.shareTypeEdit === 'core' && secRow.coreGroupSelectedEdit ||
            secRow.shareTypeEdit === 'core' && (secRow.coreShareGroupNameEdit && secRow.coreShareValueEdit))
         // && secRow.secBenifList.ben.length

        )
        :
        true
      ))) {
      this.enableStep2Submission = false;
      this.enableStep2SubmissionEdit = false;
      return false;
    } else {

      if (secRow.isReg) {

        if (!secRow.regDate) {

          this.enableStep2Submission = false;
          this.enableStep2SubmissionEdit = false;
          return false;

        } else {

          this.enableStep2Submission = true;
          this.enableStep2SubmissionEdit = true;
          return true;

        }
      } else {
        this.enableStep2Submission = true;
        this.enableStep2SubmissionEdit = true;
        return true;
      }


    }
  }

  validateSecEditForSecChangeFirm(rowId) {
    // tslint:disable-next-line:prefer-const
    let secRow = this.changeSecFirmList.secs[rowId];


    if (!(

      ((this.compayType.value === 'Public') ? secRow.pvNumber : true) &&
      secRow.firm_name &&
      secRow.firm_province &&
     // secRow.firm_district &&
      ((secRow.secType === 'firm' && !(this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ) ? secRow.firm_district : true) &&
      secRow.firm_city &&
      secRow.firm_localAddress1 &&
      secRow.firm_postcode &&
      secRow.firm_date &&
      (secRow.firm_mobile && this.phonenumber(secRow.firm_mobile, secRow.type)) &&
      (secRow.firm_email && this.validateEmail(secRow.firm_email) &&
      ((secRow.secType === 'firm' && secRow.type !== 'local' &&  (this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ) ? secRow.firm_country : true )

        &&
        (secRow.isShareholderEdit) ?
        (
          (secRow.shareTypeEdit === 'single' && secRow.noOfSingleSharesEdit ||
            secRow.shareTypeEdit === 'core' && secRow.coreGroupSelectedEdit ||
            secRow.shareTypeEdit === 'core' && (secRow.coreShareGroupNameEdit && secRow.coreShareValueEdit))
         // && secRow.secBenifList.ben.length

        )
        :
        true
      ))) {
      this.enableStep2Submission = false;
      this.enableStep2SubmissionEdit = false;
      return false;
    } else {

      if (secRow.isReg) {

        if (!secRow.regDate) {

          this.enableStep2Submission = false;
          this.enableStep2SubmissionEdit = false;
          return false;

        } else {

          this.enableStep2Submission = true;
          this.enableStep2SubmissionEdit = true;
          return true;

        }
      } else {
        this.enableStep2Submission = true;
        this.enableStep2SubmissionEdit = true;
        return true;
      }


    }
  }


  validateSecForiegn() {
    if (  ! (this.sec.passport && this.sec.passport_issued_country &&
     this.sec.title && this.sec.firstname && this.sec.lastname &&
     this.sec.forProvince && this.sec.forCity && this.sec.forAddress1 && this.sec.country &&
     this.sec.province && this.sec.district && this.sec.city && this.sec.localAddress1 && this.sec.postcode &&
     this.sec.date &&
     this.sec.mobile && this.phonenumber(this.sec.mobile, 'foreign') &&
     this.sec.email && this.validateEmail(this.sec.email)
    ) ){
     this.secValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
     this.validSec = false;
     return false;
    } else {
     if (this.sec.isReg) {
       if (!this.sec.regDate) {
         this.secValitionMessage = 'Please add the registration Number';
         this.validSec = false;
         return false;
       } else {
         this.secValitionMessage = '';
         this.validSec = true;
         return true;
       }
     } else {
       this.secValitionMessage = '';
       this.validSec = true;
       return true;
     }
    }
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
        this.sh.mobile && this.phonenumber(this.sh.mobile, this.sh.type)
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
        ( this.isGuarantyCompany ? true : ( this.sh.shareType && (this.sh.coreGroupSelected || this.sh.noOfShares || (this.sh.coreShareGroupName && this.sh.noOfSharesGroup) ) ) )
        // && this.benifList.ben.length

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
        ( this.isGuarantyCompany ? true : ( this.sh.shareType && (this.sh.coreGroupSelected || this.sh.noOfShares || (this.sh.coreShareGroupName && this.sh.noOfSharesGroup) ) ) )
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
        this.sh.firm_province &&
        this.sh.firm_localAddress1 &&
        this.sh.firm_postcode &&
        this.sh.firm_date &&
        this.sh.firm_email && this.validateEmail(this.sh.firm_email) &&
        this.sh.firm_mobile && this.phonenumber(this.sh.firm_mobile, this.sh.type) &&
        ( this.isGuarantyCompany ? true : ( this.sh.shareType && (this.sh.coreGroupSelected || this.sh.noOfShares || (this.sh.coreShareGroupName && this.sh.noOfSharesGroup) ) ) )
        // && this.benifList.ben.length

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
  validateShareHolder_inactive() {

    if (this.sh_inactive.type === 'local' && this.sh_inactive.shareholderType === 'natural') {

      if (!(this.sh_inactive.nic && this.validateNIC(this.sh_inactive.nic) &&
      ( this.isGuarantyCompany ? true : ( this.sh_inactive.shareType && (this.sh_inactive.coreGroupSelected || this.sh_inactive.noOfShares || (this.sh_inactive.coreShareGroupName && this.sh_inactive.noOfSharesGroup) ) ) )
        &&
        this.sh_inactive.title &&
        this.sh_inactive.email && this.validateEmail(this.sh_inactive.email) &&
        this.sh_inactive.firstname &&
        this.sh_inactive.lastname &&
        this.sh_inactive.province &&
        this.sh_inactive.district &&
        this.sh_inactive.city &&
        this.sh_inactive.localAddress1 &&
        this.sh_inactive.postcode &&
        this.sh_inactive.date &&
        this.sh_inactive.mobile && this.phonenumber(this.sh_inactive.mobile, this.sh_inactive.type)
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
    if (this.sh_inactive.type === 'local' && this.sh_inactive.shareholderType === 'firm') {

      if (!(this.sh_inactive.firm_name &&
        this.sh_inactive.firm_city &&
        this.sh_inactive.firm_district &&
        this.sh_inactive.firm_province &&
        this.sh_inactive.firm_localAddress1 &&
        this.sh_inactive.firm_postcode &&
        this.sh_inactive.firm_date &&
        this.sh_inactive.firm_email && this.validateEmail(this.sh_inactive.firm_email) &&
        this.sh_inactive.firm_mobile && this.phonenumber(this.sh_inactive.firm_mobile, this.sh_inactive.type) &&
        ( this.isGuarantyCompany ? true : ( this.sh_inactive.shareType && (this.sh_inactive.coreGroupSelected || this.sh_inactive.noOfShares || (this.sh_inactive.coreShareGroupName && this.sh_inactive.noOfSharesGroup) ) ) )
        // && this.benifList.ben.length

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

    if (this.sh_inactive.type === 'foreign' && this.sh_inactive.shareholderType === 'natural' ) {
      if (!(this.sh_inactive.passport && !this.isShInactiveAlreadyExist('foreign') && this.sh_inactive.passport_issued_country &&
        this.sh_inactive.title &&
        this.sh_inactive.email && this.validateEmail(this.sh_inactive.email) &&
        this.sh_inactive.firstname &&
        this.sh_inactive.lastname &&
        this.sh_inactive.country &&
        this.sh_inactive.forCity &&
        this.sh_inactive.forProvince &&
        this.sh_inactive.forAddress1 &&
        this.sh_inactive.forPostcode &&
        this.sh_inactive.date &&
        this.sh_inactive.mobile && this.phonenumber(this.sh_inactive.mobile, this.sh_inactive.type) &&
        ( this.isGuarantyCompany ? true : ( this.sh_inactive.shareType && (this.sh_inactive.coreGroupSelected || this.sh_inactive.noOfShares || (this.sh_inactive.coreShareGroupName && this.sh_inactive.noOfSharesGroup) ) ) )
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

    if (this.sh_inactive.type === 'foreign' && this.sh_inactive.shareholderType === 'firm') {

      if (!(this.sh_inactive.firm_name &&
        this.sh_inactive.firm_city &&
        this.sh_inactive.firm_province &&
        this.sh_inactive.firm_localAddress1 &&
        this.sh_inactive.firm_postcode &&
        this.sh_inactive.firm_date &&
        this.sh_inactive.firm_email && this.validateEmail(this.sh_inactive.firm_email) &&
        this.sh_inactive.firm_mobile && this.phonenumber(this.sh_inactive.firm_mobile, this.sh_inactive.type) &&
        ( this.isGuarantyCompany ? true : ( this.sh_inactive.shareType && (this.sh_inactive.coreGroupSelected || this.sh_inactive.noOfShares || (this.sh_inactive.coreShareGroupName && this.sh_inactive.noOfSharesGroup) ) ) )
        // && this.benifList.ben.length

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
        ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares || (shRow.coreShareGroupName && shRow.noOfSharesGroup)) )
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
        ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares || (shRow.coreShareGroupName && shRow.noOfSharesGroup)) )
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
       ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares || (shRow.coreShareGroupName && shRow.noOfSharesGroup)) )
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
        shRow.firm_province &&
        shRow.firm_localAddress1 &&
        shRow.firm_postcode &&
        shRow.firm_date &&
        shRow.firm_email && this.validateEmail(shRow.firm_email) &&
        shRow.firm_mobile && this.phonenumber(shRow.firm_mobile, shRow.type ) &&
       //  shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares)
       ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares || (shRow.coreShareGroupName && shRow.noOfSharesGroup)) )
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
  validateShareHolder_inactiveEdit(rowId, isFirm = false) {

    // tslint:disable-next-line:prefer-const
    let shRow = (isFirm) ? this.shFirmList_inactive.shs[rowId] :  this.shList_inactive.shs[rowId];

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
        ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares || (shRow.coreShareGroupName && shRow.noOfSharesGroup)) )
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
        ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares || (shRow.coreShareGroupName && shRow.noOfSharesGroup)) )
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
       ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares || (shRow.coreShareGroupName && shRow.noOfSharesGroup)) )
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
        shRow.firm_province &&
        shRow.firm_localAddress1 &&
        shRow.firm_postcode &&
        shRow.firm_date &&
        shRow.firm_email && this.validateEmail(shRow.firm_email) &&
        shRow.firm_mobile && this.phonenumber(shRow.firm_mobile, shRow.type ) &&
       //  shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares)
       ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares || (shRow.coreShareGroupName && shRow.noOfSharesGroup)) )
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
  validateSecShBenif() {
    if (this.sec.benifOwnerType === 'local') {
      if (!
        (
          this.sec_sh_benif.nic && this.validateNIC(this.sec_sh_benif.nic) &&
          this.sec_sh_benif.title &&
          this.sec_sh_benif.email && this.validateEmail(this.sec_sh_benif.email) &&
          this.sec_sh_benif.firstname &&
          this.sec_sh_benif.lastname &&
          this.sec_sh_benif.province &&
          this.sec_sh_benif.district &&
          this.sec_sh_benif.city &&
          this.sec_sh_benif.date &&
          this.sec_sh_benif.mobile && this.phonenumber(this.sec_sh_benif.mobile, this.sec.benifOwnerType) &&
          this.sec_sh_benif.localAddress1 &&
          this.sec_sh_benif.postcode

        )

      ) {
        this.validateSecShBenifFlag = false;
        return false;
      } else {
        this.validateSecShBenifFlag = true;
        return true;

      }

    }

    if (this.sec.benifOwnerType === 'foreign') {

      if (!(this.sec_sh_benif.passport &&
        this.sec_sh_benif.title &&
        this.sec_sh_benif.email && this.validateEmail(this.sec_sh_benif.email) &&
        this.sec_sh_benif.firstname &&
        this.sec_sh_benif.lastname &&
        this.sec_sh_benif.province &&

        this.sec_sh_benif.city &&
        this.sec_sh_benif.country &&
        this.sec_sh_benif.mobile && this.phonenumber(this.sec_sh_benif.mobile, this.sec.benifOwnerType) &&
        this.sec_sh_benif.localAddress1 &&
        this.sec_sh_benif.date &&
        this.sec_sh_benif.postcode
      )) {

        this.validateSecShBenifFlag = false;
        return false;

      } else {
        this.validateSecShBenifFlag = true;
        return true;

      }

    }
  }

  validateSecShBenifEdit(i) {
    if (this.secFirmList.secs[i].benifOwnerType === 'local') {
      if (!
        (
          this.sec_sh_benif.nic && this.validateNIC(this.sec_sh_benif.nic) &&
          this.sec_sh_benif.title &&
          this.sec_sh_benif.email && this.validateEmail(this.sec_sh_benif.email) &&
          this.sec_sh_benif.firstname &&
          this.sec_sh_benif.lastname &&
          this.sec_sh_benif.province &&
          this.sec_sh_benif.district &&
          this.sec_sh_benif.city &&
          this.sec_sh_benif.mobile && this.phonenumber(this.sec_sh_benif.mobile, this.secFirmList.secs[i].benifOwnerType ) &&
          this.sec_sh_benif.localAddress1 &&
          this.sec_sh_benif.date &&
          this.sec_sh_benif.postcode
        )

      ) {
        this.secFirmList.secs[i].validateSecShBenifInEdit = false;
        return false;
      } else {
        this.secFirmList.secs[i].validateSecShBenifInEdit = true;
        return true;

      }

    }

    if (this.secFirmList.secs[i].benifOwnerType === 'foreign') {

      if (!(this.sec_sh_benif.passport &&
        this.sec_sh_benif.title &&
        this.sec_sh_benif.email && this.validateEmail(this.sec_sh_benif.email) &&
        this.sec_sh_benif.firstname &&
        this.sec_sh_benif.lastname &&
        this.sec_sh_benif.province &&

        this.sec_sh_benif.city &&
        this.sec_sh_benif.country &&
        this.sec_sh_benif.mobile && this.phonenumber(this.sec_sh_benif.mobile, this.secFirmList.secs[i].benifOwnerType ) &&
        this.sec_sh_benif.localAddress1 &&
       this.sec_sh_benif.date &&
        this.sec_sh_benif.postcode
      )) {

        this.secFirmList.secs[i].validateSecShBenifInEdit = false;
        return false;

      } else {
        this.secFirmList.secs[i].validateSecShBenifInEdit = true;
        return true;

      }

    }
  }

  addSecShBenificiaries() {
    if (this.sec.benifOwnerType === 'foreign') {
      this.sec_sh_benif.type = 'foreign';
    } else {
      this.sec_sh_benif.type = 'local';
    }
    // tslint:disable-next-line:prefer-const
    let copy1 = Object.assign({}, this.sec_sh_benif);
    this.secBenifList.ben.push(copy1);
    this.secBenifList.ben.reverse();
    this.validateSec();
    this.sec_sh_benif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '' , screen1Districts: [], screen1Cities: [] };
    this.validateSecShBenif();

  }

  addSecShBenificiariesEdit(i) {

    if (this.secFirmList.secs[i].benifOwnerType === 'foreign') {
      this.sec_sh_benif.type = 'foreign';
    } else {
      this.sec_sh_benif.type = 'local';
    }
    // tslint:disable-next-line:prefer-const
    let copy1 = Object.assign({}, this.sec_sh_benif);
    this.secFirmList.secs[i].secBenifList.ben.push(copy1);
    this.secFirmList.secs[i].secBenifList.ben.reverse();
    this.validateSecEditForSecFirm(i);
    this.sec_sh_benif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '',   screen1Districts: [], screen1Cities: [] };
    this.validateSecShBenifEdit(i);

  }

  removeExistingDirectorRecord(userId: number = 0) {

    if ( !confirm('Are you sure you want to remove this director?') ) {
      return true;
    }

   // this.directorList.directors.splice(i, 1);
    if (!userId) {
      return true;
    }

    const data = {
      company_id: this.companyId,
      director_id: userId,
      reason_info: this.removeStakeholderInfo
    };

    this.annualService.removeExistingDirector(data)
      .subscribe(
        req => {

          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(2);
            return true;
          } else {
             alert('Failed Removing this director. Please try  again.');
             this.changeProgressStatuses(2);
             this.loadData();
             return false;
          }
          this.valideRemoveStakeholderInfo = false;
          this.resetRemoveStakeholderInfo();
          this.removeStakeholderId = null;
          this.reoveStakeholderType = '';
          this.removeStakeholderUserType = '';
          this.removeStakeholderAppointmentDate =  '';
        },
        error => {
          this.changeProgressStatuses(2);
          console.log(error);
          this.valideRemoveStakeholderInfo = false;
          this.resetRemoveStakeholderInfo();
          this.removeStakeholderId = null;
          this.reoveStakeholderType = '';
          this.removeStakeholderUserType = '';
          this.removeStakeholderAppointmentDate = '';
        }

      );

  }

  openRemoveStakeholderPopUp(userId, type= 'existing', userType= '', appointmentDate = '' ) {
    this.valideRemoveStakeholderInfo = false;
    this.resetRemoveStakeholderInfo();
    this.removeStakeholderId = userId;
    this.reoveStakeholderType = type;
    this.removeStakeholderUserType = userType;
    this.removeStakeholderAppointmentDate = appointmentDate;

  }

  removeChangeDirectorWithReason(userId: number = 0) {
    if (!userId) {
      return true;
    }

    if ( !confirm('Are you sure you want to remove this director?') ) {
      return true;
    }

    const data = {
      company_id: this.companyId,
      director_id: userId,
      reason_info: this.removeStakeholderInfo
    };

    this.annualService.removeChangeDirector(data)
      .subscribe(
        req => {

          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(2);
            return true;
          } else {
             alert('Failed Removing this director. Please try  again.');
             this.changeProgressStatuses(2);
             this.loadData();
             return false;
          }

          this.valideRemoveStakeholderInfo = false;
          this.resetRemoveStakeholderInfo();
          this.removeStakeholderId = null;
          this.reoveStakeholderType = '';
          this.removeStakeholderUserType = '';
          this.removeStakeholderAppointmentDate = '';
        },
        error => {
          this.changeProgressStatuses(2);
          console.log(error);
          this.valideRemoveStakeholderInfo = false;
          this.resetRemoveStakeholderInfo();
          this.removeStakeholderId = null;
          this.reoveStakeholderType = '';
          this.removeStakeholderUserType = '';
          this.removeStakeholderAppointmentDate = '';
        }

      );
  }

  removeChangeDirectorRecord(i: number, userId: number = 0) {

    if ( !confirm('Are you sure you want to remove this director?') ) {
      return true;
    }
   // this.directorList.directors.splice(i, 1);
    if (!userId) {
      return true;
    }

    const data = {
      company_id: this.companyId,
      director_id: userId,
    };

    this.annualService.removeChangeDirector(data)
      .subscribe(
        req => {

          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(1);
            return true;
          } else {
             alert('Failed Removing this director. Please try  again.');
             this.changeProgressStatuses(1);
             this.loadData();
             return false;
          }
        },
        error => {
          this.changeProgressStatuses(1);
          console.log(error);
        }

      );

  }

  editExisingDirector(i: number, userId: number = 0, type = 'existing') {

    if ( !confirm('Are you sure you want to update information of  this director?') ) {
      return true;
    }

   // this.directorList.directors.splice(i, 1);
   // if (!userId) {
    //  return true;
   // }

    const data = {
      company_id: this.companyId,
      director_id: userId,
      director: (type === 'change') ? this.changeDirectorList.directors[i] : this.directorList.directors[i],
      type: type
    };

    this.annualService.updateExistingDirector(data)
      .subscribe(
        req => {

          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(2);
            return true;
          } else {
             alert('Failed Updating this director. Please try  again.');
             this.changeProgressStatuses(2);
             this.loadData();
             return false;
          }
        },
        error => {
          this.changeProgressStatuses(2);
          console.log(error);
        }

      );
  }

  removeDirectorRecord(i: number, userId: number = 0) {

    if ( !confirm('Are you sure you want to remove this director?') ) {
      return true;
    }

    this.directorList.directors.splice(i, 1);
    if (!userId) {
      return true;
    }
    this.submitDirectors('remove');

  }

  addNewDirectorRecord() {

    if ( !confirm('Are you sure you want to add this new director?') ) {
      return true;
    }

    const data = {
      company_id: this.companyId,
      director: this.director
    };

    this.annualService.addNewDirector(data)
      .subscribe(
        req => {

          // tslint:disable-next-line:max-line-length
          this.director = { id: 0, secRegDate: '', showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', screen1Districts: [], screen1Cities: [] , passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec: true };

          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(2);
            return true;
          } else {
             alert('Failed Updating this director. Please try  again.');
             this.changeProgressStatuses(2);
             this.loadData();
             return false;
          }
        },
        error => {
          this.changeProgressStatuses(2);
          console.log(error);
        }

      );
  }





  removeExistingSecRecord(userId: number = 0, type = 'sec' ) {

    let confirmMessaage = (type === 'secFirm') ? 'Are you sure you want to remove this legal person?' : 'Are you sure you want to remove this power of attorney holder';

    if ( !confirm(confirmMessaage) ) {
      return true;
    }

   // this.directorList.directors.splice(i, 1);
    if (!userId) {
      return true;
    }

    const data = {
      company_id: this.companyId,
      sec_id: userId,
      type: type,
      reason_info: this.removeStakeholderInfo
    };

    this.annualService.removeExistingSec(data)
      .subscribe(
        req => {

          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(3);
            return true;
          } else {
             alert('Failed Removing this stakeholder. Please try  again.');
             this.changeProgressStatuses(3);
             this.loadData();
             return false;
          }
          this.valideRemoveStakeholderInfo = false;
          this.resetRemoveStakeholderInfo();
          this.removeStakeholderId = null;
          this.reoveStakeholderType = '';
          this.removeStakeholderUserType = '';
          this.removeStakeholderAppointmentDate =  '';
        },
        error => {
          this.changeProgressStatuses(2);
          console.log(error);
          this.valideRemoveStakeholderInfo = false;
          this.resetRemoveStakeholderInfo();
          this.removeStakeholderId = null;
          this.reoveStakeholderType = '';
          this.removeStakeholderUserType = '';
          this.removeStakeholderAppointmentDate =  '';
        }

      );

  }

  editExisingSec(i: number, userId: number = 0, type = 'existing') {

    if ( !confirm('Are you sure you want to update information of  this power of attorney holder?') ) {
      return true;
    }

    const data = {
      company_id: this.companyId,
      sec_id: userId,
      sec: (type === 'change') ? this.changeSecList.secs[i] : this.secList.secs[i],
      type: type
    };

    this.annualService.updateExistingSec(data)
      .subscribe(
        req => {

          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(3);
            return true;
          } else {
             alert('Failed Updating this stakeholder. Please try  again.');
             this.changeProgressStatuses(3);
             this.loadData();
             return false;
          }
        },
        error => {
          this.changeProgressStatuses(3);
          console.log(error);
        }

      );
  }


  removeChangeSecWithReason(userId: number = 0, type = 'sec') {

    let confirmMessaage = (type === 'secFirm') ? 'Are you sure you want to remove this legal person?' : 'Are you sure you want to remove this power of attorney holder';

    if ( !confirm(confirmMessaage) ) {
      return true;
    }
    if (!userId) {
      return true;
    }

    const data = {
      company_id: this.companyId,
      sec_id: userId,
      type: type,
      reason_info: this.removeStakeholderInfo
    };

    this.annualService.removeChangeSec(data)
      .subscribe(
        req => {

          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(3);
            return true;
          } else {
             alert('Failed Removing this director. Please try  again.');
             this.changeProgressStatuses(3);
             this.loadData();
             return false;
          }

          this.valideRemoveStakeholderInfo = false;
          this.resetRemoveStakeholderInfo();
          this.removeStakeholderId = null;
          this.reoveStakeholderType = '';
          this.removeStakeholderUserType = '';
          this.removeStakeholderAppointmentDate =  '';
        },
        error => {
          this.changeProgressStatuses(3);
          console.log(error);
          this.valideRemoveStakeholderInfo = false;
          this.resetRemoveStakeholderInfo();
          this.removeStakeholderId = null;
          this.reoveStakeholderType = '';
          this.removeStakeholderUserType = '';
          this.removeStakeholderAppointmentDate =  '';
        }

      );
  }

  removeChangeSecRecord(i: number, userId: number = 0, type = 'sec' ) {

    let confirmMessaage = (type === 'secFirm') ? 'Are you sure you want to remove this legal person?' : 'Are you sure you want to remove this power of attorney holder';

    if ( !confirm(confirmMessaage) ) {
      return true;
    }

   // this.directorList.directors.splice(i, 1);
    if (!userId) {
      return true;
    }

    const data = {
      company_id: this.companyId,
      sec_id: userId,
      type: type
    };

    this.annualService.removeChangeSec(data)
      .subscribe(
        req => {

          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(3);
            return true;
          } else {
             alert('Failed Removing this stakeholder. Please try  again.');
             this.changeProgressStatuses(3);
             this.loadData();
             return false;
          }
        },
        error => {
          this.changeProgressStatuses(3);
          console.log(error);
        }

      );

  }

  editExisingSecFirm(i: number, userId: number = 0, type = 'existing') {

    if ( !confirm('Are you sure you want to update information of  this power of attorney holder firm?') ) {
      return true;
    }

    const data = {
      company_id: this.companyId,
      sec_id: userId,
      sec: (type === 'change') ? this.changeSecFirmList.secs[i] : this.secFirmList.secs[i],
      type: type
    };

    this.annualService.updateExistingSecFirm(data)
      .subscribe(
        req => {

          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(3);
            return true;
          } else {
             alert('Failed Updating this stakeholder firm. Please try  again.');
             this.changeProgressStatuses(3);
             this.loadData();
             return false;
          }
        },
        error => {
          this.changeProgressStatuses(3);
          console.log(error);
        }

      );
  }

  removeSecRecord(i: number, userId: number = 0) {

    if ( !confirm('Are you sure you want to remove this secretory?') ) {
      return true;
    }

    this.secList.secs.splice(i, 1);
    if (!userId) {
      return true;
    }
    this.submitSecretories('remove');

  }

  removeSecFirmRecord(i: number, userId: number = 0) {

    if ( !confirm('Are you sure you want to remove this secretory/legal person ?') ) {
      return true;
    }

    this.secFirmList.secs.splice(i, 1);
    if (!userId) {
      return true;
    }
    this.submitSecretories('remove');

  }

  addNewSecRecord() {

    if ( !confirm('Are you sure you want to add this new power of attoarny holder?') ) {
      return true;
    }

    const data = {
      company_id: this.companyId,
      sec: this.sec
    };

    this.annualService.addNewSec(data)
      .subscribe(
        req => {

          // tslint:disable-next-line:max-line-length
          this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', benifOwnerType : 'local', screen1Districts: [], screen1Cities: [], validateSecShBenifInEdit : false, secBenifList : { ben : [] } , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '', firm_date : '' };

          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(3);
            return true;
          } else {
             alert('Failed Updating this power of attorny. Please try  again.');
             this.changeProgressStatuses(3);
             this.loadData();
             return false;
          }
        },
        error => {
          this.changeProgressStatuses(3);
          console.log(error);
        }

      );
  }

  addNewSecFirmRecord() {

    if ( !confirm('Are you sure you want to add this new power of attoarny holder firm?') ) {
      return true;
    }

    const data = {
      company_id: this.companyId,
      sec: this.sec
    };

    this.annualService.addNewSecFirm(data)
      .subscribe(
        req => {

          // tslint:disable-next-line:max-line-length
          this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', benifOwnerType : 'local', screen1Districts: [], screen1Cities: [], validateSecShBenifInEdit : false, secBenifList : { ben : [] } , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '', firm_date : '' };

          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(3);
            return true;
          } else {
             alert('Failed Updating this power of attorny firm. Please try  again.');
             this.changeProgressStatuses(3);
             this.loadData();
             return false;
          }
        },
        error => {
          this.changeProgressStatuses(3);
          console.log(error);
        }

      );
  }

  updateOtherDocDate(action = '') {

    const data = {
      company_id: this.companyId,
      article_date: this.article_date,
      memorandum_date: this.memorandum_date,
      statute_date: this.statute_date,
      charter_date: this.charter_date
    };

    this.annualService.updateOtherDocDate(data)
      .subscribe(
        req => {

          if (req['status'] ) {
            this.loadData();

            if (action === '' ) {
              this.changeProgressStatuses(5);
            } else {
              this.changeProgressStatuses(4);
            }

            return true;
          } else {
            // alert('Failed Updating this power of attorny firm. Please try  again.');
             this.changeProgressStatuses(4);
             this.other_docs_change_date = '';
             this.loadData();
             return false;
          }
        },
        error => {
          this.changeProgressStatuses(4);
          console.log(error);
        }

      );

  }


  /*********util functions  ********/
  checkNIC(memberType: number = 1, secShBen = false) {


    this.directorNicLoaded = false;
    this.secNicLoaded = false;
    this.shNicLoaded = false;
    this.loadNICstakeholders = false;
    this.openAddressPart = false;

    // tslint:disable-next-line:prefer-const
    let checker = (memberType === 1) ? this.director.nic : (memberType === 2) ? this.sec.nic : this.sh.nic;
    if (secShBen) {
      checker = this.sec.nic;
    }
    // tslint:disable-next-line:prefer-const
    let type = (memberType === 1) ? this.director.type : (memberType === 2) ? this.sec.type : this.sh.type;

    if (!checker) {
      this.directorNicLoaded = false;
      this.secNicLoaded = false;
      this.shNicLoaded = false;
      this.loadNICstakeholders = false;
      this.openAddressPart = false;
      return false;
    }

    if (type !== 'local') {
      this.directorNicLoaded = true;
      this.secNicLoaded = true;
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


    this.annualService.annualNICcheck(data)
      .subscribe(
        req => {

          this.loadNICstakeholders = false;
          this.openAddressPart = req['data']['openLocalAddress'];

          if (memberType === 1) {

            if (req['status'] && req['data']['member_count'] === 1) {

              this.director.firstname = req['data']['member_record'][0]['first_name'];
            //  this.director.title = 'Mr.';
              this.director.lastname = req['data']['member_record'][0]['last_name'];
              this.director.email = req['data']['member_record'][0]['email'];
              this.director.country = req['data']['member_record'][0]['passport_issued_country'];
              this.director.nic = req['data']['member_record'][0]['nic'];


              this.director.province = (undefined === req['data']['address_record']['province'] || !req['data']['address_record']['province']) ? null : req['data']['address_record']['province'];

              this.director.district = (undefined === req['data']['address_record']['district'] || !req['data']['address_record']['district']) ? null : req['data']['address_record']['district'];
              this.director.city = (undefined === req['data']['address_record']['city'] || !req['data']['address_record']['city']) ? null : req['data']['address_record']['city'];
              this.director.localAddress1 = (undefined === req['data']['address_record']['address1'] || !req['data']['address_record']['address1']) ? ''  : req['data']['address_record']['address1'];
              this.director.localAddress2 = (undefined === req['data']['address_record']['address2'] || !req['data']['address_record']['address2']) ? ''  : req['data']['address_record']['address2'];
              this.director.postcode = (undefined === req['data']['address_record']['postcode'] || !req['data']['address_record']['postcode']) ? ''  : req['data']['address_record']['postcode'];

              this.director.passport = req['data']['member_record'][0]['passport_no'];
              this.director.phone = req['data']['member_record'][0]['telephone'];
              this.director.mobile = req['data']['member_record'][0]['mobile'];
              this.director.share = req['data']['member_record'][0]['no_of_shares'];
              this.director.date = '';
              this.director.occupation = req['data']['member_record'][0]['occupation'];
              this.director.title =  req['data']['title'];
              this.director.id = 0;
              this.director.showEditPaneForDirector = 0;

              this.directorNicLoaded = true;


              if ( this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' ||
                this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34' ||
                this.compayType.key === 'COMPANY_TYPE_PUBLIC' ) {

                  if (req['data']['sec_reg_no']  ) {
                    this.director.secRegDate = req['data']['sec_reg_no'];
                    this.guarantee_sec_err_happend = false;
                  } else {
                    this.director.secRegDate = '';
                    this.guarantee_sec_err_happend = true;
                  }
              }

              this.getProvincesForStakeHolder('director');
              this.getDistrictsForStakeholder('director', this.director.province, true );
              this.getCitiesForStakeholder( 'director', this.director.district, true );

              this.loadNICstakeholders = true;

              this.validateDirector();

            } else { // reset
              // tslint:disable-next-line:max-line-length
              this.director = { id: 0, showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: null, noOfSingleShares: null, coreGroupSelected: null, coreShareGroupName: '', coreShareValue: null, screen1Districts: [], screen1Cities: [], passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec: true };

              if ( this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' ||
                this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34' ||
                this.compayType.key === 'COMPANY_TYPE_PUBLIC' ) {

                  if (req['data']['sec_reg_no']  ) {
                    this.director.secRegDate = req['data']['sec_reg_no'];
                    this.guarantee_sec_err_happend = false;
                  } else {
                    this.guarantee_sec_err_happend = true;
                  }
              }

              this.getProvincesForStakeHolder('director');
              this.getDistrictsForStakeholder('director', this.director.province );
              this.getCitiesForStakeholder( 'director', this.director.district );
              this.director.nic = checker;
              this.directorNicLoaded = true;
              this.loadNICstakeholders = false;
              this.openAddressPart = false;
            }

            return true;

          }

          if (memberType === 2) {

            if (req['status'] && req['data']['member_count'] === 1) {
             // this.sec.title = 'Mr.';
              this.sec.title =  req['data']['title'];
              this.sec.firstname = req['data']['member_record'][0]['first_name'];
              this.sec.lastname = req['data']['member_record'][0]['last_name'];
              this.sec.email = req['data']['member_record'][0]['email'];
              this.sec.country = req['data']['member_record'][0]['passport_issued_country'];
              this.sec.nic = req['data']['member_record'][0]['nic'];


              this.sec.province = req['data']['address_record']['province'];
              this.sec.district = req['data']['address_record']['district'];
              this.sec.city = req['data']['address_record']['city'];
              this.sec.localAddress1 = req['data']['address_record']['address1'];
              this.sec.localAddress2 = req['data']['address_record']['address2'];
              this.sec.postcode = req['data']['address_record']['postcode'];

              this.sec.passport = req['data']['member_record'][0]['passport_no'];
              this.sec.phone = req['data']['member_record'][0]['telephone'];
              this.sec.mobile = req['data']['member_record'][0]['mobile'];
              this.sec.share = req['data']['member_record'][0]['no_of_shares'];
              this.sec.date = '';
              // this.sec.date = (this.sec.date === '1970-01-01') ? '' : this.sec.date;
              this.sec.occupation = req['data']['member_record'][0]['occupation'];
              this.sec.isReg = (req['data']['member_record'][0]['is_registered_secretary'] === 'yes') ? true : false;
              this.sec.regDate = (req['data']['member_record'][0]['secretary_registration_no']) ? req['data']['member_record'][0]['secretary_registration_no'] :  this.sec.regDate = req['data']['sec_reg_no'];

              if (this.sec.regDate) {
                this.sec.isReg = true;
              }

              if ( this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' ||
                this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34' ||
                this.compayType.key === 'COMPANY_TYPE_PUBLIC' ) {
                  this.sec.regDate = req['data']['sec_reg_no'];

                  if ( this.sec.regDate  ) {
                    this.sec.isReg  = true;
                    this.guarantee_sec_err_happend = false;

                  } else {
                    this.guarantee_sec_err_happend = true;
                    // tslint:disable-next-line:max-line-length
                    this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', benifOwnerType : 'local', screen1Districts: [], screen1Cities: [], validateSecShBenifInEdit : false, secBenifList : { ben : [] } , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '', firm_date : '' };
                    this.sec.nic = checker;
                    this.secNicLoaded = false;
                    this.loadNICstakeholders = false;
                    this.openAddressPart = false;
                    return false;
                  }
                }

              this.sec.secType = (req['data']['member_record'][0]['company_member_firm_id']) ? 'firm' : 'natural';
              this.getProvincesForStakeHolder('sec');
              this.getDistrictsForStakeholder('sec', this.sec.province, true );
              this.getCitiesForStakeholder( 'sec', this.sec.district, true );
              this.validateSec();
              this.secNicLoaded = true;
              this.loadNICstakeholders = true;
            } else { // reset

              // tslint:disable-next-line:max-line-length
              this.sec = { secType: 'natural', id: 0, showEditPaneForSec: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', isShareholder: false, shareType: null, noOfSingleShares: null, coreGroupSelected: null, coreShareGroupName: '', coreShareValue: null, secCompanyFirmId: '', pvNumber: '', firm_name: '', firm_province: null, firm_district: null, firm_city: null, firm_localAddress1: null, firm_localAddress2: null, firm_postcode: null, screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '', firm_date : '' };
              this.getProvincesForStakeHolder('sec');
              this.getDistrictsForStakeholder('sec', this.sec.province );
              this.getCitiesForStakeholder( 'sec', this.sec.district );
              this.sec.nic = checker;

              if ( this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' ||
                this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34' ||
                this.compayType.key === 'COMPANY_TYPE_PUBLIC' ) {
                  this.guarantee_sec_err_happend = true;
                  this.secNicLoaded = false;
                }else {
                  this.secNicLoaded = true;
                  this.guarantee_sec_err_happend = false;
                }

              this.loadNICstakeholders = false;

            }
            return true;

          }

          if (memberType === 3) {

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
              // this.sh.share = req['data']['member_record'][0]['no_of_shares'];
              this.sh.date = '';
             // this.sh.date = (this.sh.date === '1970-01-01') ? '' : this.sh.date;
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
              this.sh = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', screen1Districts: [], screen1Cities: [],  noOfShares: 0, shareholderType: 'natural', benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: ''};
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

          }

          if (memberType === 33) {

            if (req['status'] && req['data']['member_count'] === 1) {

              // this.sh.title = 'Mr.';
              this.sh_inactive.title =  req['data']['title'];
              this.sh_inactive.firstname = req['data']['member_record'][0]['first_name'];
              this.sh_inactive.lastname = req['data']['member_record'][0]['last_name'];
              this.sh_inactive.email = req['data']['member_record'][0]['email'];
              this.sh_inactive.country = req['data']['member_record'][0]['passport_issued_country'];
              this.sh_inactive.nic = req['data']['member_record'][0]['nic'];


              this.sh_inactive.province = req['data']['address_record']['province'];
              this.sh_inactive.district = req['data']['address_record']['district'];
              this.sh_inactive.city = req['data']['address_record']['city'];
              this.sh_inactive.localAddress1 = req['data']['address_record']['address1'];
              this.sh_inactive.localAddress2 = req['data']['address_record']['address2'];
              this.sh_inactive.postcode = req['data']['address_record']['postcode'];

              this.sh_inactive.passport = req['data']['member_record'][0]['passport_no'];
              this.sh_inactive.phone = req['data']['member_record'][0]['telephone'];
              this.sh_inactive.mobile = req['data']['member_record'][0]['mobile'];
              // this.sh.share = req['data']['member_record'][0]['no_of_shares'];
              this.sh_inactive.date = '';
             // this.sh.date = (this.sh.date === '1970-01-01') ? '' : this.sh.date;
              this.sh_inactive.occupation = req['data']['member_record'][0]['occupation'];

              this.getProvincesForStakeHolder('sh_inactive');
              this.getDistrictsForStakeholder('sh_inactive', this.sh_inactive.province, true );
              this.getCitiesForStakeholder( 'sh_inactive', this.sh_inactive.district, true );

              this.validateShareHolder_inactive();

              if (secShBen) {
                this.secNicLoaded = true;
              } else {
                this.shNicLoaded = true;
              }
              this.loadNICstakeholders = true;

            } else { // reset
              // tslint:disable-next-line:max-line-length
              this.sh_inactive = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', screen1Districts: [], screen1Cities: [],  noOfShares: 0, shareholderType: 'natural', benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: ''};
              this.getProvincesForStakeHolder('sh_inactive');
              this.getDistrictsForStakeholder('sh_inactive', this.sh.province );
              this.getCitiesForStakeholder( 'sh_inactive', this.sh.district );
              if (secShBen) {
                this.secNicLoaded = true;
              } else {
                this.shNicLoaded = true;
              }
              this.sh_inactive.nic = checker;
              this.loadNICstakeholders = false;
              this.openAddressPart = false;
            }

            return true;

          }


        },
        error => {
          console.log(error);
        }

      );
  }

  checkNICForSecShFirmEdit(i) {

    this.secNicLoaded = false;
    // tslint:disable-next-line:prefer-const
    let checker = this.secFirmList.secs[i].nic;

    if (!checker) {
      this.secNicLoadedEdit = -1;
      this.validateSecEditForSecFirm(i);
      return false;
    }

    const data = {
      companyId: this.companyId,
      nic: checker,
      memberType: 3

    };

    this.annualService.annualNICcheck(data)
      .subscribe(
        req => {

          // if (req['status'] && req['data']['member_count'] === 1) {

          this.secFirmList.secs[i].title = (req['status'] && req['data']['member_count'] === 1) ? req['data']['member_record'][0]['title'] : '';

          this.secFirmList.secs[i].firstname = (req['status'] && req['data']['member_count'] === 1) ? req['data']['member_record'][0]['first_name'] : '';
          this.secFirmList.secs[i].lastname = (req['status'] && req['data']['member_count'] === 1) ? req['data']['member_record'][0]['last_name'] : '';
          this.secFirmList.secs[i].email = (req['status'] && req['data']['member_count'] === 1) ? req['data']['member_record'][0]['email'] : '';
          this.secFirmList.secs[i].country = (req['status'] && req['data']['member_count'] === 1) ? req['data']['member_record'][0]['passport_issued_country'] : '';
          // this.secFirmList.secs[i].nic = (req['status'] && req['data']['member_count'] === 1) ? req['data']['member_record'][0]['nic'] : '';

          this.secFirmList.secs[i].province = (req['status'] && req['data']['member_count'] === 1) ? req['data']['address_record']['province'] : '';
          this.secFirmList.secs[i].district = (req['status'] && req['data']['member_count'] === 1) ? req['data']['address_record']['district'] : '';
          this.secFirmList.secs[i].city = (req['status'] && req['data']['member_count'] === 1) ? req['data']['address_record']['city'] : '';
          this.secFirmList.secs[i].localAddress1 = (req['status'] && req['data']['member_count'] === 1) ? req['data']['address_record']['address1'] : '';
          this.secFirmList.secs[i].localAddress2 = (req['status'] && req['data']['member_count'] === 1) ? req['data']['address_record']['address2'] : '';
          this.secFirmList.secs[i].postcode = (req['status'] && req['data']['member_count'] === 1) ? req['data']['address_record']['postcode'] : '';

          this.secFirmList.secs[i].phone = (req['status'] && req['data']['member_count'] === 1) ? req['data']['member_record'][0]['telephone'] : '';
          this.secFirmList.secs[i].mobile = (req['status'] && req['data']['member_count'] === 1) ? req['data']['member_record'][0]['mobile'] : '';

          this.secFirmList.secs[i].date = (req['status'] && req['data']['member_count'] === 1) ? req['data']['member_record'][0]['date_of_appointment'] : '';
          this.secFirmList.secs[i].date = (this.secFirmList.secs[i].date === '1970-01-01') ? '' : this.secFirmList.secs[i].date;
          this.secFirmList.secs[i].occupation = (req['status'] && req['data']['member_count'] === 1) ? req['data']['member_record'][0]['occupation'] : '';

          this.secFirmList.secs[i].isReg = false;
          this.secFirmList.secs[i].regDate = '';

          this.validateSecEditForSecFirm(i);
          this.secNicLoadedEdit = i;

          //  } else { // reset
          //  this.secFirmList.secs[i] = { isReg: false, regDate: '', id: 0 , showEditPaneForSec: 0,  type: 'local' , title: '' , firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: this.sh.nic, passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '',  shareType: 'single', };
          //  this.secNicLoaded = true;
          //  }

          return true;

        },
        error => {
          console.log(error);
        }

      );
  }

  private removeDuplicatesByNIC(memberType = 1) {

    if (memberType === 1) {

      // tslint:disable-next-line:prefer-const
      let copy = Object.assign({}, this.director);

      // tslint:disable-next-line:prefer-const
      for (let i in this.directorList.directors) {

        if (this.directorList.directors[i]['nic'] === copy['nic']) {
          let index;
          // tslint:disable-next-line:radix
          index = parseInt(i);
          this.directorList.directors.splice(index, 1);

        }
      }

      return true;

    }

    if (memberType === 2) {

      // tslint:disable-next-line:prefer-const
      let copy = Object.assign({}, this.sec);

      // tslint:disable-next-line:prefer-const
      for (let i in this.secList.secs) {

        if (this.secList.secs[i]['nic'] === copy['nic']) {
          let index;
          // tslint:disable-next-line:radix
          index = parseInt(i);
          this.secList.secs.splice(index, 1);

        }
      }

      return true;

    }

    if (memberType === 3) {

      // tslint:disable-next-line:prefer-const
      let copy = Object.assign({}, this.sh);

      // tslint:disable-next-line:prefer-const
      for (let i in this.shList.shs) {

        if (this.shList.shs[i]['nic'] === copy['nic']) {
          let index;
          // tslint:disable-next-line:radix
          index = parseInt(i);
          this.shList.shs.splice(index, 1);

        }
      }

      return true;

    }

  }

  directorsNicList() {

    // tslint:disable-next-line:prefer-const
    let directors = this.directorList.directors;
    // tslint:disable-next-line:prefer-const
    let directorNICList = {
      'local': [],
      'foreign': []
    };

    if (!directors.length) {
      return directorNICList;
    }

    // tslint:disable-next-line:prefer-const
    for (let i in directors) {

      if (directors[i].type === 'local') {
        directorNICList.local.push(directors[i].nic.toLowerCase());
      }

      if (directors[i].type === 'foreign') {
        directorNICList.foreign.push(directors[i].passport.toLowerCase());
      }

    }

    return directorNICList;

  }

  isDirectorAlreadyExist(directorType = 'local') {

    const directorList = this.directorsNicList();

    const directorLocalList = directorList.local;
    const directorForeignList = directorList.foreign;

    if (directorType === 'foreign') {

      return (directorForeignList.indexOf(this.director.passport.toLowerCase()) > -1);
    } else if (directorType === 'local') {

      return (directorLocalList.indexOf(this.director.nic.toLowerCase()) > -1);
    } else {
      return false;
    }

  }

  isDirectorAlreadyExistAction(directorType = 'local') {

    // tslint:disable-next-line:prefer-const
    let message = (directorType === 'foreign') ?
      'This Director Already Exists. Please Try a Different passport number' :
      'This Director Already Exists. Please try a Different NIC';

    if (this.isDirectorAlreadyExist(directorType)) {

      if (directorType === 'local') {
        this.directorNicLoaded = false;
      }
      // this.checkNIC(1);
      this.directorAlreadyExistMessage = message;
    } else {
      this.directorAlreadyExistMessage = '';
      if (directorType === 'local') {
        this.checkNIC(1);
      }
    }

  }


  secNicList() {

    // tslint:disable-next-line:prefer-const
    let secs = this.secList.secs;
    // tslint:disable-next-line:prefer-const
    let secNICList = {
      'local': [],
    };

    if (!secs.length) {
      return secNICList;
    }
    // tslint:disable-next-line:prefer-const
    for (let i in secs) {

      if (secs[i].nic) {
        secNICList.local.push(secs[i].nic.toLowerCase());
      }

    }
    return secNICList;
  }

  isSecAlreadyExist() {

    const secList = this.secNicList();
    const secLocalList = secList.local;
    return ( this.sec.nic && secLocalList.indexOf(this.sec.nic.toLowerCase()) > -1);
  }

  isSecAlreadyExistForDirector(nic) {

    const secList = this.secNicList();
    const secLocalList = secList.local;
    return ( nic && secLocalList.indexOf(nic.toLowerCase()) > -1);
  }


  isSecShareAlreadyExistAction() { // sec as a shareholder

    // tslint:disable-next-line:prefer-const
    let message = 'This Shareholder Already Exists. Please try a Different NIC';
    // tslint:disable-next-line:prefer-const
    let shType = 'local';
    // tslint:disable-next-line:prefer-const
    let secType = this.sec.secType;

    if (this.isShAlreadyExist(shType)) {
      this.shAlreadyExistMessage = message;
      this.secNicLoaded = false;
    } else {
      this.shAlreadyExistMessage = '';

      this.checkNIC(3, true);
    }
    if (secType === 'firm') {
      this.sec.secType = 'firm';
    }
  }

  isSecShareAlreadyExistActionEdit(i) { // sec as a shareholder

    // tslint:disable-next-line:prefer-const
    let message = 'This Shareholder Already Exists. Please Try a Different NIC';
    // tslint:disable-next-line:prefer-const
    let shType = 'local';

    if (this.isShAlreadyExist(shType)) {
      this.shAlreadyExistMessage = message;
      this.secNicLoaded = false;
    } else {
      this.shAlreadyExistMessage = '';
      this.checkNICForSecShFirmEdit(i);
    }

    this.validateSecEditForSecFirm(i);
    this.secFirmList.secs[i].secType = 'firm';
  }

  isSecAlreadyExistAction() {

    // tslint:disable-next-line:prefer-const
    let message = 'This Secretory Already Exists. Please Try a Different NIC';

    if (this.isSecAlreadyExist()) {
      this.secNicLoaded = false;
      this.secAlreadyExistMessage = message;
    } else {
      this.secAlreadyExistMessage = '';
      this.checkNIC(2);
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
  shareholderInactiveNicList() {

    // tslint:disable-next-line:prefer-const
    let shs = this.shList_inactive.shs;
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
  isShInactiveAlreadyExist(shType = 'local') {

    const shList = this.shareholderInactiveNicList();

    const shLocalList = shList.local;
    const shForeignList = shList.foreign;

    if (shType === 'foreign') {
      return (shForeignList.indexOf(this.sh_inactive.passport.toLowerCase()) > -1);
    } else if (shType === 'local') {
      return (shLocalList.indexOf(this.sh_inactive.nic.toLowerCase()) > -1);
    } else {
      return false;
    }

  }

  isShAlreadyExistForDirector(shType = 'local', nicOrPassport ) {

    const shList = this.shareholderNicList();

    const shLocalList = shList.local;
    const shForeignList = shList.foreign;

    if (shType === 'foreign') {
      return (shForeignList.indexOf(nicOrPassport.toLowerCase()) > -1);
    } else if (shType === 'local') {
      return (shLocalList.indexOf(nicOrPassport.toLowerCase()) > -1);
    } else {
      return false;
    }

  }

  isShAlreadyExistForSec(shType = 'local') {

    const shList = this.shareholderNicList();
    const shLocalList = shList.local;
    return (shLocalList.indexOf(this.sec.nic.toLowerCase()) > -1);
  }

  isShAlreadyExistForSecAction(shType = 'local') {

    // tslint:disable-next-line:prefer-const
    let message = (shType === 'foreign') ?
      'This Shareholder Already Exist. Please Try Different Passport number' :
      'This Shareholder Already Exist. Please Try Different NIC';

    if (this.isShAlreadyExistForSec(shType)) {
      this.secAlreadyExistMessage = message;
      this.secNicLoaded = false;
    } else {
      this.secAlreadyExistMessage = '';
      this.checkNIC(3);
      this.secNicLoaded = true;
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

      // if (shType === 'foreign') {
       // this.shNicLoaded = true;
      //  this.sh = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, shareholderType: 'natural', benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } };
      //  return true;
     // }

      if (shType === 'local') {
        this.checkNIC(3);
      }
    }
  }

  isShInacriveAlreadyExistAction(shType = 'local') {

    // tslint:disable-next-line:prefer-const
    let message = (shType === 'foreign') ?
      'This Shareholder Already Exists. Please Try a Different Passport number' :
      'This Shareholder Already Exists. Please Try a different NIC';

    if (this.isShInactiveAlreadyExist(shType)) {
      this.shAlreadyExistMessage = message;

      if (shType === 'local') {
        this.shNicLoaded = false;
      } else {
        this.shNicLoaded = true;
      }
    } else {
      this.shAlreadyExistMessage = '';

      // if (shType === 'foreign') {
       // this.shNicLoaded = true;
      //  this.sh = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, shareholderType: 'natural', benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } };
      //  return true;
     // }

      if (shType === 'local') {
        this.checkNIC(33);
      }
    }
  }


  private arraysEqual(_arr1, _arr2) {

    if (!Array.isArray(_arr1) || !Array.isArray(_arr2) || _arr1.length !== _arr2.length) {
      return false;
    }
    // tslint:disable-next-line:prefer-const
    let arr1 = _arr1.concat().sort();
    // tslint:disable-next-line:prefer-const
    let arr2 = _arr2.concat().sort();

    for (let i = 0; i < arr1.length; i++) {

      if (arr1[i] !== arr2[i]) {
        return false;
      }
    }

    return true;

  }


  private slugify(text) {
    return text.toString().toLowerCase()
      .replace(/\s+/g, '-')           // Replace spaces with -
      .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
      .replace(/\-\-+/g, '-')         // Replace multiple - with single -
      .replace(/^-+/, '')             // Trim - from start of text
      .replace(/-+$/, '');            // Trim - from end of text
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


  fileChange(event, fileNane, fileDBID, member_type = null, member_id = null  ) {


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

      if (member_type === 'member') {
        formData.append('member_type',  'member' );
        formData.append('member_id',  member_id);
      }
      if (member_type === 'firm') {
        formData.append('member_type',  'firm' );
        formData.append('member_id',  member_id);
      }
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


  removeDoc( docTypeId, membeType = null , memberId = null ) {

    let removeConf = confirm('Are you sure, you want to delete uploaded document ?');

    if (!removeConf) {
      return false;
    }

    const data = {
      companyId: this.companyId,
      fileTypeId: docTypeId,
    };
    if ( membeType === 'member') {
      data['member_type'] = 'member';
      data['member_id'] = memberId;
    }
    if ( membeType === 'firm') {
      data['member_type'] = 'firm';
      data['member_id'] = memberId;
    }
    this.spinner.show();
    this.annualService.removeDoc(data)
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

  removeOtherDoc(token){
    const data = {
      file_token: token ,
    };
    this.spinner.show();

    // load Company data from the server
    this.annualService.removeOtherDoc(data)
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


  /**************Share Register Functions************************/

  showToggleShareRegisterRecord(srId = 0) {

      // tslint:disable-next-line:prefer-const
      for (let i in this.shareRegisterList.sr) {
        if (this.shareRegisterList.sr[i]['id'] === srId) {
          this.shareRegisterList.sr[i]['showEditPane'] = this.shareRegisterList.sr[i]['showEditPane'] === srId ? null : srId;
          return true;
        }
      }
  }
  resetShareRegister() {

    this.shareRegister = {id: null, showEditPane: 0, description: '', address_id: null, foreign_address_id: null, address_type: 'local', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forProvince: '', forCity: '', forPostcode: '', country: 'Sri Lanka'};
    this.getProvincesForShareRegister();
  }

  validateShareRegisterEdit(i){
    let row = this.shareRegisterList.sr[i];

    if (!
      (
        row.description &&
        row.province &&
        row.district &&
        row.city &&
        row.localAddress1 &&
        row.postcode
      )
    ) {

      this.shareRegisterValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.enableStep2Submission = false;
      this.enableStep2SubmissionEdit = false;
      return false;
    } else {

      this.shareRegisterValitionMessage = '';
      this.enableStep2Submission = true;
      this.enableStep2SubmissionEdit = true;
      return true;

    }

  }

  validateShareRegister() {
      if (!
        (
          this.shareRegister.description &&
          this.shareRegister.province &&
          this.shareRegister.district &&
          this.shareRegister.city &&
          this.shareRegister.localAddress1 &&
          this.shareRegister.postcode
        )
      ) {
        this.shareRegisterValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.validShareRegister = false;
        return false;
      } else {

        this.shareRegisterValitionMessage = '';
        this.validShareRegister = true;
        return true;

      }
  }

  getProvincesForShareRegister() {

    // tslint:disable-next-line:prefer-const
    let provinces = Object.assign({}, this.provinces);
    // tslint:disable-next-line:prefer-const
    let filterProvince: Array<IProvince> = [];
   // tslint:disable-next-line:prefer-const
   for ( let i in provinces ) {
    filterProvince.push( provinces[i]);
    }

       this.shareRegister.screen1Provinces = filterProvince;
       this.validateShareRegister();

  }

  getDistrictsForShareRegister(provinceName, load = false ) {

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

    this.shareRegister.screen1Districts = filterDistricts;

     if (load === false ) {
      this.shareRegister.city = '';
      this.shareRegister.district = '';
     }

    this.validateShareRegister();
  }

  getCitiesForShareRegiter(districtName, load = false ) {

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
      this.shareRegister.screen1Cities = filterCities;
      if (load === false ) {
        this.shareRegister.city = '';
       }
      this.validateShareRegister();

   }

   ////// edit
   getProvincesForShareRegisterEdit( i) {

    // tslint:disable-next-line:prefer-const
    let provinces = Object.assign({}, this.provinces);
    // tslint:disable-next-line:prefer-const
    let filterProvince: Array<IProvince> = [];
   // tslint:disable-next-line:prefer-const
   for ( let j in provinces ) {
    filterProvince.push( provinces[j]);
    }

    this.shareRegisterList.sr[i].screen1Provinces = filterProvince;
    this.validateShareRegisterEdit(i);

  }

  getDistrictsForShareEdit( i , provinceName, load = false ) {

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

    this.shareRegisterList.sr[i].screen1Districts = filterDistricts;
     if (load === false ) {
      this.shareRegisterList.sr[i].city = '';
      this.shareRegisterList.sr[i].district = '';
     }

    this.validateShareRegisterEdit(i);
  }

  getCitiesForShareRegisterEdit(i, districtName, load = false ) {

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

    this.shareRegisterList.sr[i].screen1Cities = filterCities;
    if (load === false ) {
        this.shareRegisterList.sr[i].city = '';
    }
    this.validateShareRegisterEdit(i);

   }

   saveShareRegisterRecord() {

    // tslint:disable-next-line:prefer-const
    let copy = Object.assign({}, this.shareRegister);
    this.shareRegisterList.sr.push(copy);

    // tslint:disable-next-line:max-line-length
    this.shareRegister = {id: null, showEditPane: 0, description: '', address_id: null, foreign_address_id: null, address_type: 'local', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forProvince: '', forCity: '', forPostcode: '', country: 'Sri Lanka'};
    this.validShareRegister = false;
    this.submitShareReigsters('remove');

  }

  submitShareReigsters(action = '') {

    const data = {
      companyId: this.companyId,
      loginUser: this.loginUserEmail,
      share_registers: this.shareRegisterList,
    };

    this.annualService.annualShareRegisterSubmit(data)
      .subscribe(
        req => {
          this.loadData();
          if (action === 'remove') { // in case of removing stakeholder, keep the position on same page.
            this.changeProgressStatuses(1);
            return false;
          }
          this.changeProgressStatuses(2);
        },
        error => {
          this.changeProgressStatuses(1);
          console.log(error);
        }

      );


  }

  removeShareRegisterRecord(i: number, srId: number = 0) {

    if ( !confirm('Are you sure you want to remove this record?') ) {
      return true;
    }

    this.shareRegisterList.sr.splice(i, 1);
    if (!srId) {
      return true;
    }
    this.submitShareReigsters('remove');

  }


  /**************End Share Register Functions*******************/


 /**************Annual Records Functions************************/

    showToggleAnnualRecord(recId = 0) {

      // tslint:disable-next-line:prefer-const
      for (let i in this.annualRecordList.rec) {
        if (this.annualRecordList.rec[i]['id'] === recId) {
          this.annualRecordList.rec[i]['showEditPane'] = this.annualRecordList.rec[i]['showEditPane'] === recId ? null : recId;
          return true;
        }
      }
  }
  resetAnnualRecord() {

    this.annualRecord = {id: null, showEditPane: 0, description: '', address_id: null, foreign_address_id: null, address_type: 'local', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forProvince: '', forCity: '', forPostcode: '', country: 'Sri Lanka'};
    this.getProvincesForAnnualRecord();
  }

  validateAnnualRecordEdit(i){
    let row = this.annualRecordList.rec[i];

    if (!
      (
        row.description &&
        row.province &&
        row.district &&
        row.city &&
        row.localAddress1 &&
        row.postcode
      )
    ) {

      this.annualRecordValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.enableStep2Submission = false;
      this.enableStep2SubmissionEdit = false;
      return false;
    } else {

      this.annualRecordValitionMessage = '';
      this.enableStep2Submission = true;
      this.enableStep2SubmissionEdit = true;
      return true;

    }
  }

  validateAnnualRecord() {
      if (!
        (
          this.annualRecord.description &&
          this.annualRecord.province &&
          this.annualRecord.district &&
          this.annualRecord.city &&
          this.annualRecord.localAddress1 &&
          this.annualRecord.postcode
        )
      ) {
        this.annualRecordValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.validAnnualRecord = false;
        return false;
      } else {

        this.annualRecordValitionMessage = '';
        this.validAnnualRecord = true;
        return true;

      }
  }

  getProvincesForAnnualRecord() {

    // tslint:disable-next-line:prefer-const
    let provinces = Object.assign({}, this.provinces);
    // tslint:disable-next-line:prefer-const
    let filterProvince: Array<IProvince> = [];
   // tslint:disable-next-line:prefer-const
   for ( let i in provinces ) {
    filterProvince.push( provinces[i]);
    }

       this.annualRecord.screen1Provinces = filterProvince;
       this.validateAnnualRecord();

  }

  getDistrictsForAnnualRecord(provinceName, load = false ) {

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

    this.annualRecord.screen1Districts = filterDistricts;

     if (load === false ) {
      this.annualRecord.city = '';
      this.annualRecord.district = '';
     }

    this.validateAnnualRecord();
  }

  getCitiesForAnnualRecord(districtName, load = false ) {

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
      this.annualRecord.screen1Cities = filterCities;
      if (load === false ) {
        this.annualRecord.city = '';
       }
      this.validateAnnualRecord();

   }

   ////// edit
   getProvincesForAnnualRecordEdit( i) {

    // tslint:disable-next-line:prefer-const
    let provinces = Object.assign({}, this.provinces);
    // tslint:disable-next-line:prefer-const
    let filterProvince: Array<IProvince> = [];
   // tslint:disable-next-line:prefer-const
   for ( let j in provinces ) {
    filterProvince.push( provinces[j]);
    }

    this.annualRecordList.rec[i].screen1Provinces = filterProvince;
    this.validateAnnualRecordEdit(i);

  }

  getDistrictsForAnnualRecordEdit( i , provinceName, load = false ) {

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

    this.annualRecordList.rec[i].screen1Districts = filterDistricts;
     if (load === false ) {
      this.annualRecordList.rec[i].city = '';
      this.annualRecordList.rec[i].district = '';
     }

    this.validateAnnualRecordEdit(i);
  }

  getCitiesForAnnualRecordEdit(i, districtName, load = false ) {

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

    this.annualRecordList.rec[i].screen1Cities = filterCities;
    if (load === false ) {
        this.annualRecordList.rec[i].city = '';
    }
    this.validateAnnualRecordEdit(i);

   }

   saveAnnualRecord() {

    // tslint:disable-next-line:prefer-const
    let copy = Object.assign({}, this.annualRecord);
    this.annualRecordList.rec.push(copy);

    // tslint:disable-next-line:max-line-length
    this.annualRecord = {id: null, showEditPane: 0, description: '', address_id: null, foreign_address_id: null, address_type: 'local', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forProvince: '', forCity: '', forPostcode: '', country: 'Sri Lanka'};
    this.validAnnualRecord = false;
    this.submitAnnualRecords('remove');

  }

  submitAnnualRecords(action = '') {

    const data = {
      companyId: this.companyId,
      loginUser: this.loginUserEmail,
      annual_records: this.annualRecordList,
    };

    this.annualService.annualRecordsSubmit(data)
      .subscribe(
        req => {
          this.loadData();
          if (action === 'remove') {
            this.changeProgressStatuses(2);
            return false;
          }
          this.changeProgressStatuses(3);
        },
        error => {
          this.changeProgressStatuses(2);
          console.log(error);
        }

      );


  }

  removeAnnualRecord(i: number, recId: number = 0) {

    if ( !confirm('Are you sure you want to remove this record?') ) {
      return true;
    }

    this.annualRecordList.rec.splice(i, 1);
    if (!recId) {
      return true;
    }
    this.submitAnnualRecords('remove');

  }


  /**************End Annual record Functions*******************/


   /**************Annual Auditor Functions************************/

   showToggleAnnualAuditorRecord(recId = 0) {

    // tslint:disable-next-line:prefer-const
    for (let i in this.auditorList.member) {
      if (this.auditorList.member[i]['id'] === recId) {
        this.auditorList.member[i]['showEditPane'] = this.auditorList.member[i]['showEditPane'] === recId ? null : recId;
        return true;
      }
    }
}
resetAnnualAuditorRecord() {

  this.auditor = {id: null, showEditPane: 0, first_name: '', last_name: '', address_id: null, foreign_address_id: null, address_type: 'local', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forProvince: '', forCity: '', forPostcode: '', country: 'Sri Lanka'};
  this.getProvincesForAnnualAuditorRecord();
}

validateAnnualAuditorRecordEdit(i){
  let row = this.auditorList.member[i];

    if (!
      (
        row.first_name &&
        row.last_name &&
        row.province &&
        row.district &&
        row.city &&
        row.localAddress1 &&
        row.postcode
      )
    ) {

      this.annualAuditorRecordValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.enableStep2Submission = false;
      this.enableStep2SubmissionEdit = false;
      return false;
    } else {

      this.annualAuditorRecordValitionMessage = '';
      this.enableStep2Submission = true;
      this.enableStep2SubmissionEdit = true;
      return true;

    }
}

validateAnnualAuditorRecord() {
    if (!
      (
        this.auditor.first_name &&
        this.auditor.last_name &&
        this.auditor.province &&
        this.auditor.district &&
        this.auditor.city &&
        this.auditor.localAddress1 &&
        this.auditor.postcode
      )
    ) {
      this.annualAuditorRecordValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validAnnualAuditorRecord = false;
      return false;
    } else {

      this.annualAuditorRecordValitionMessage = '';
      this.validAnnualAuditorRecord = true;
      return true;

    }
}

getProvincesForAnnualAuditorRecord() {

  // tslint:disable-next-line:prefer-const
  let provinces = Object.assign({}, this.provinces);
  // tslint:disable-next-line:prefer-const
  let filterProvince: Array<IProvince> = [];
 // tslint:disable-next-line:prefer-const
 for ( let i in provinces ) {
  filterProvince.push( provinces[i]);
  }

     this.auditor.screen1Provinces = filterProvince;
     this.validateAnnualAuditorRecord();

}

getDistrictsForAnnualAuditorRecord(provinceName, load = false ) {

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

  this.auditor.screen1Districts = filterDistricts;

   if (load === false ) {
    this.auditor.city = '';
    this.auditor.district = '';
   }

  this.validateAnnualAuditorRecord();
}

getCitiesForAnnualAuditorRecord(districtName, load = false ) {

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
    this.auditor.screen1Cities = filterCities;
    if (load === false ) {
      this.auditor.city = '';
     }
    this.validateAnnualAuditorRecord();

 }

 ////// edit
 getProvincesForAnnualAuditorRecordEdit( i) {

  // tslint:disable-next-line:prefer-const
  let provinces = Object.assign({}, this.provinces);
  // tslint:disable-next-line:prefer-const
  let filterProvince: Array<IProvince> = [];
 // tslint:disable-next-line:prefer-const
 for ( let j in provinces ) {
  filterProvince.push( provinces[j]);
  }

  this.auditorList.member[i].screen1Provinces = filterProvince;
  this.validateAnnualAuditorRecordEdit(i);

}

getDistrictsForAnnualAuditorRecordEdit( i , provinceName, load = false ) {

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

  this.auditorList.member[i].screen1Districts = filterDistricts;
   if (load === false ) {
    this.auditorList.member[i].city = '';
    this.auditorList.member[i].district = '';
   }

  this.validateAnnualAuditorRecordEdit(i);
}

getCitiesForAnnualAuditorRecordEdit(i, districtName, load = false ) {

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

  this.auditorList.member[i].screen1Cities = filterCities;
  if (load === false ) {
      this.auditorList.member[i].city = '';
  }
  this.validateAnnualAuditorRecordEdit(i);

 }

 saveAnnualAuditorRecord() {

  // tslint:disable-next-line:prefer-const
  let copy = Object.assign({}, this.auditor);
  this.auditorList.member.push(copy);

  // tslint:disable-next-line:max-line-length
  this.auditor = {id: null, showEditPane: 0, first_name: '', last_name: '', address_id: null, foreign_address_id: null, address_type: 'local', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forProvince: '', forCity: '', forPostcode: '', country: 'Sri Lanka'};
  this.validAnnualAuditorRecord = false;
  this.submitAnnualAuditorRecords('remove');

}

submitAnnualAuditorRecords(action = '') {

  const data = {
    companyId: this.companyId,
    loginUser: this.loginUserEmail,
    auditor_records: this.auditorList,
  };

  this.annualService.annualAuditorSubmit(data)
    .subscribe(
      req => {
        this.loadData();
        if (action === 'remove') {
          this.changeProgressStatuses((this.isGuarantyCompany ? 5 : 6));
          return false;
        }
        this.changeProgressStatuses((this.isGuarantyCompany ? 6 : 7));
      },
      error => {
        this.changeProgressStatuses((this.isGuarantyCompany ? 5 : 6));
        console.log(error);
      }

    );


}

removeAnnualAuditorRecord(i: number, recId: number = 0) {

  if ( !confirm('Are you sure you want to remove this record?') ) {
    return true;
  }

  this.auditorList.member.splice(i, 1);
  if (!recId) {
    return true;
  }
  this.submitAnnualAuditorRecords('remove');

}


/**************End Annual auditor Functions*******************/

 /**************Annual Charges Functions************************/

 showToggleAnnualChargeRecord(recId = 0) {

  // tslint:disable-next-line:prefer-const
  for (let i in this.chargeList.ch) {
    if (this.chargeList.ch[i]['id'] === recId) {
      this.chargeList.ch[i]['showEditPane'] = this.chargeList.ch[i]['showEditPane'] === recId ? null : recId;
      return true;
    }
  }
}
resetAnnualChargeRecord() {

this.charge = {id: null, showEditPane: 0, name: '', date: '', description: '' , amount: '', address_id: null, foreign_address_id: null, address_type: 'local', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forProvince: '', forCity: '', forPostcode: '', country: 'Sri Lanka'};
this.getProvincesForAnnualChargeRecord();
}

validateAnnualChargeRecordEdit(i){
  let row = this.chargeList.ch[i];

  if (!
    (
      row.name &&
      row.date &&
      row.description &&
      row.amount && parseFloat(row.amount) &&
      row.province &&
      row.district &&
      row.city &&
      row.localAddress1 &&
      row.postcode
    )
  ) {

    this.annualChargeRecordValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
    this.enableStep2Submission = false;
    this.enableStep2SubmissionEdit = false;
    return false;
  } else {

    this.annualChargeRecordValitionMessage = '';
    this.enableStep2Submission = true;
    this.enableStep2SubmissionEdit = true;
    return true;

  }
}

validateAnnualChargeRecord() {
  if (!
    (
      this.charge.name &&
      this.charge.date &&
      this.charge.description &&
      this.charge.amount && parseFloat(this.charge.amount) &&
      this.charge.province &&
      this.charge.district &&
      this.charge.city &&
      this.charge.localAddress1 &&
      this.charge.postcode
    )
  ) {
    this.annualChargeRecordValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
    this.validAnnualChargeRecord = false;
    return false;
  } else {

    this.annualChargeRecordValitionMessage = '';
    this.validAnnualChargeRecord = true;
    return true;

  }
}

getProvincesForAnnualChargeRecord() {

// tslint:disable-next-line:prefer-const
let provinces = Object.assign({}, this.provinces);
// tslint:disable-next-line:prefer-const
let filterProvince: Array<IProvince> = [];
// tslint:disable-next-line:prefer-const
for ( let i in provinces ) {
filterProvince.push( provinces[i]);
}

   this.charge.screen1Provinces = filterProvince;
   this.validateAnnualChargeRecord();

}

getDistrictsForAnnualChargeRecord(provinceName, load = false ) {

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

this.charge.screen1Districts = filterDistricts;

 if (load === false ) {
  this.charge.city = '';
  this.charge.district = '';
 }

this.validateAnnualChargeRecord();
}

getCitiesForAnnualChargeRecord(districtName, load = false ) {

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
  this.charge.screen1Cities = filterCities;
  if (load === false ) {
    this.charge.city = '';
   }
  this.validateAnnualChargeRecord();

}

////// edit
getProvincesForAnnualChargeRecordEdit( i) {

// tslint:disable-next-line:prefer-const
let provinces = Object.assign({}, this.provinces);
// tslint:disable-next-line:prefer-const
let filterProvince: Array<IProvince> = [];
// tslint:disable-next-line:prefer-const
for ( let j in provinces ) {
filterProvince.push( provinces[j]);
}

this.chargeList.ch[i].screen1Provinces = filterProvince;
this.validateAnnualChargeRecordEdit(i);

}

getDistrictsForAnnualChargeRecordEdit( i , provinceName, load = false ) {

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

this.chargeList.ch[i].screen1Districts = filterDistricts;
 if (load === false ) {
  this.chargeList.ch[i].city = '';
  this.chargeList.ch[i].district = '';
 }

this.validateAnnualChargeRecordEdit(i);
}

getCitiesForAnnualChargeRecordEdit(i, districtName, load = false ) {

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

this.chargeList.ch[i].screen1Cities = filterCities;
if (load === false ) {
    this.chargeList.ch[i].city = '';
}
this.validateAnnualChargeRecordEdit(i);

}

saveAnnualChargeRecord() {

// tslint:disable-next-line:prefer-const
let copy = Object.assign({}, this.charge);
this.chargeList.ch.push(copy);

// tslint:disable-next-line:max-line-length
this.charge = {id: null, showEditPane: 0, name: '', date: '', description: '' , amount: '', address_id: null, foreign_address_id: null, address_type: 'local', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forProvince: '', forCity: '', forPostcode: '', country: 'Sri Lanka'};
this.validAnnualChargeRecord = false;
this.submitAnnualChargeRecords('remove');

}

submitAnnualChargeRecords(action = '') {

const data = {
  companyId: this.companyId,
  loginUser: this.loginUserEmail,
  charges_records: this.chargeList,
};

this.annualService.annualChargeSubmit(data)
  .subscribe(
    req => {
      this.loadData();
      if (action === 'remove') {
        this.changeProgressStatuses((this.isGuarantyCompany ? 8 : 9));
        return false;
      }
      this.changeProgressStatuses((this.isGuarantyCompany ? 9 : 10));
    },
    error => {
      this.changeProgressStatuses((this.isGuarantyCompany ? 8 : 9));
      console.log(error);
    }

  );


}

removeAnnualChargeRecord(i: number, recId: number = 0) {

if ( !confirm('Are you sure you want to remove this record?') ) {
  return true;
}

this.chargeList.ch.splice(i, 1);
if (!recId) {
  return true;
}
this.submitAnnualChargeRecords('remove');

}

/**************End Annual charges Functions*******************/


  /**************Annual Share Functions************************/

  showToggleShareRecord(recId = 0) {

    // tslint:disable-next-line:prefer-const
    for (let i in this.shareList.share) {
      if (this.shareList.share[i]['id'] === recId) {
        this.shareList.share[i]['showEditPane'] = this.shareList.share[i]['showEditPane'] === recId ? null : recId;
        return true;
      }
    }
}
resetShareRecord() {

  this.shareItem = {id: null, showEditPane: 0, issue_type_as_cash: true, issue_type_as_non_cash: false , share_class: '', share_consideration_value_paid: '', no_of_shares: '', share_value: '', share_consideration: '' , shares_issued_for_cash: '', shares_issued_for_non_cash: '', shares_called_on: ''};
}

validateShareRecordEdit(i){
  let row = this.shareList.share[i];

    if (!
      (
        // tslint:disable-next-line:radix
        row.share_class && row.no_of_shares && parseInt(row.no_of_shares) &&
        // tslint:disable-next-line:radix
        ( row.issue_type_as_non_cash || row.issue_type_as_cash ) &&  row.shares_called_on && parseInt(row.shares_called_on) &&
        // tslint:disable-next-line:radix
        ( row.issue_type_as_non_cash ? (row.share_consideration && row.shares_issued_for_non_cash && parseInt(row.shares_issued_for_non_cash) && row.share_consideration_value_paid && parseFloat(row.share_consideration_value_paid)) : true ) &&
        // tslint:disable-next-line:radix
        ( row.issue_type_as_cash ? ( row.shares_issued_for_cash && row.shares_issued_for_cash && parseInt(row.shares_issued_for_cash) && parseFloat(row.share_value)) : true )
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

validateShareRecord() {
    if (!
      (
        // tslint:disable-next-line:radix
        this.shareItem.share_class && this.shareItem.no_of_shares && parseInt(this.shareItem.no_of_shares) &&
        // tslint:disable-next-line:radix
        ( this.shareItem.issue_type_as_non_cash || this.shareItem.issue_type_as_cash ) &&  this.shareItem.shares_called_on && parseInt(this.shareItem.shares_called_on) &&
        // tslint:disable-next-line:radix
        ( this.shareItem.issue_type_as_non_cash ? (this.shareItem.share_consideration && this.shareItem.shares_issued_for_non_cash && parseInt(this.shareItem.shares_issued_for_non_cash) && this.shareItem.share_consideration_value_paid && parseFloat(this.shareItem.share_consideration_value_paid)) : true ) &&
        // tslint:disable-next-line:radix
        ( this.shareItem.issue_type_as_cash ? ( this.shareItem.shares_issued_for_cash && this.shareItem.shares_issued_for_cash && parseInt(this.shareItem.shares_issued_for_cash) && parseFloat(this.shareItem.share_value)) : true )
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
  let copy = Object.assign({}, this.shareItem);
  this.shareList.share.push(copy);

  // tslint:disable-next-line:max-line-length
  this.shareItem = {id: null, showEditPane: 0, issue_type_as_cash: true, issue_type_as_non_cash: false , share_class: '', share_consideration_value_paid: '', no_of_shares: '', share_value: '', share_consideration: '' , shares_issued_for_cash: '', shares_issued_for_non_cash: '', shares_called_on: ''};
  this.validShareRecordItem = false;
  this.submitShareRecord('remove');

}

submitShareRecord(action = '') {

  const data = {
    companyId: this.companyId,
    loginUser: this.loginUserEmail,
    share_records: this.shareList,
    amount_calls_recieved: this.amount_calls_recieved,
    amount_calls_unpaid: this.amount_calls_unpaid,
    amount_calls_forfeited: this.amount_calls_forfeited,
    amount_calls_purchased: this.amount_calls_purchased,
    amount_calls_redeemed: this.amount_calls_redeemed
  };

  this.annualService.shareRecordSubmit(data)
    .subscribe(
      req => {
        this.loadData();
        if (action === 'remove') {
          this.changeProgressStatuses(3);
          return false;
        }
        this.changeProgressStatuses(4);
      },
      error => {
        this.changeProgressStatuses(4);
        console.log(error);
      }

    );

}

removeShareRecord(i: number, recId: number = 0) {

  if ( !confirm('Are you sure you want to remove this share record?') ) {
    return true;
  }

  this.shareList.share.splice(i, 1);
  if (!recId) {
    return true;
  }
  this.submitShareRecord('remove');

}


/**************End SHARE Functions*******************/


  pay() {


    this.paymentItems.push(
      {
          fee_type: 'PAYMENT_FORM35',
          description: 'Payment for Return of Alterations of Particulars of the Company',
          quantity: 1,
      }

    );

    if (this.form45_payment_enable) {
      this.paymentItems.push(
        {
            fee_type: 'PAYMENT_OVERSEAS_FORM45',
            description: 'Payment for Form 45',
            quantity: 1,
        }
      );
    }

    if (this.form46_payment_enable) {
      this.paymentItems.push(
        {
            fee_type: 'PAYMENT_OVERSEAS_FORM46',
            description: 'Payment for Form 46',
            quantity: 1,
        }
      );
    }

    const buy: IBuy = {
      module_type: 'MODULE_OVERSEAS_ALTERATIONS',
      module_id: this.requestId.toString(),
      description: 'Return of Alterations of Particulars of Overseas Company',
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

  resubmit() {
    const data = {
      companyId: this.companyId,
    };
    this.spinner.show();

    this.annualService.resubmitProcess(data)
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

  updateAlterType() {
    const data = {
      company_id: this.companyId,
      alter_type : this.alterType
    };
    this.spinner.show();

    this.annualService.updateAlterationType(data)
      .subscribe(
        req => {
         // this.loadData();
          if (req['status']) {
            this.loadData();
            this.changeProgressStatuses(1);
          }else {
            alert( 'Failed updating applied alteration. Please try again later.');
            this.changeProgressStatuses(0);
            this.spinner.hide();
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

  /**********bulk shareholder upload */

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
      let uploadurl = this.url.shareholderBulkUploadURL();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            if ( data['status']) {
              this.loadData();

            } else {
              this.spinner.hide();
            }

          },
          error => {
            alert(error);
            this.spinner.hide();
          }
        );
    }

  }


  ceasedShareholderBulkUpload(event ) {

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
      let uploadurl = this.url.CeasedShareholderBulkUploadURL();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            if ( data['status']) {
              this.loadData();

            } else {
              this.spinner.hide();
            }

          },
          error => {
            alert(error);
            this.spinner.hide();
          }
        );
    }

  }

  validateOtherDocs(action = '') {

    if (
       ( (this.charter_date && this.charter_file_upload)  || (!this.charter_date && !this.charter_file_upload) ) &&
       ( (this.statute_date && this.statute_file_upload)  || (!this.statute_date && !this.statute_file_upload) ) &&
       ( (this.memorandum_date && this.memorandum_file_upload)  || (!this.memorandum_date && !this.memorandum_file_upload) ) &&
       ( (this.article_date && this.article_file_upload)  || (!this.article_date && !this.article_file_upload) )

    ) {
       this.valide_other_doc_upload = true;

       if (action === '') {
         this.updateOtherDocDate('load');
       }

    } else {
      this.valide_other_doc_upload = false;
    }

    console.log(this.valide_other_doc_upload);
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

setAlterType(key) {
  let copyAlterOpions = Object.assign({}, this.alterOptions);

   for (let i in copyAlterOpions ) {
     if (copyAlterOpions[i].key === key ) {
        this.alterOptions[i].isSelected = !!(copyAlterOpions[i].isSelected);

          for ( let j = 0; j < this.alterType.length; j++) {
            if ( this.alterType[j] === key ) {
              this.alterType.splice(j, 1);
            }
          }
          if (this.alterOptions[i].isSelected) {
            this.alterType.push(key);
          }

      }
   }

}




}
