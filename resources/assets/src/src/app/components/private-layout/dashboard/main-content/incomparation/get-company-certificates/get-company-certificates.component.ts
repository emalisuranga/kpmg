import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { IncorporationService } from '../../../../../../http/services/incorporation.service';
import { IDirectors, IDirector, ISecretories, ISecretory, IShareHolders, IShareHolder, IShareHolderBenif, IShareHolderBenifList, IProvince, IDistrict, ICity, IObjective, IGnDivision, IObjectiveRow, IObjectiveCollection } from '../../../../../../http/models/stakeholder.model';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { IcompanyInfo, IcompanyAddress, IcompanyType, IcompnayTypesItem, IcompanyObjective, IloginUserAddress, IloginUser, IcoreShareGroup, Icountry, IcompanyForAddress, IirdInfo } from '../../../../../../http/models/incorporation.model';
import { count, distinct } from 'rxjs/operators';
import { GeneralService } from '../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { DataService } from '../../../../../../storage/data.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../../http/models/payment';
import { isNull } from '@angular/compiler/src/output/output_ast';
import { environment } from '../../../../../../../environments/environment';

@Component({
  selector: 'app-get-company-certificates',
  templateUrl: './get-company-certificates.component.html',
  styleUrls: ['../incomparation.component.scss'],
})
export class GetCompanyCertificatesComponent implements OnInit, AfterViewInit {

  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;
  showIncompleteActions: boolean = environment.showIncompleteActions;
cipher_message: string;
paymentItems: Array<Item> = [];

delevery_option: string = null;
  // company id
  companyId: string;
  loginUserEmail: string;
  // process status
  processStatus: string;
  loginUserInfo: IloginUser;
  loginUserAddress: IloginUserAddress;
  // company_types
  companyTypes: Array<IcompnayTypesItem> = [];
  companyObjectives: Array<IcompanyObjective> = [];
  postFixEn = '';
  postFixSi = '';
  postFixTa = '';

  hasCopyToRequest = false;

  requesetCopies = false;

  externalGlobComment = '';

  companyInfo: IcompanyInfo = {

    abbreviation_desc: '', address_id: null, created_at: null, created_by: null, email: '', id: null, name: '', name_si: '', name_ta: '', postfix: '', status: null, type_id: null, updated_at: null, objective1: null, objective2: null, objective3: null, objective4: null, objective5: null, otherObjective: ''
  };

  companyAddress: IcompanyAddress = {
    address1: '', address2: '', gn_division: '',  city: '', country: '', created_at: '', district: '', id: 0, postcode: '', province: '', updated_at: ''
  };
  companyForAddress: IcompanyForAddress = {
    address1: '', address2: '', city: '', country: '', created_at: '', district: '', province: '', updated_at: ''
  };

  compayType: IcompanyType = { key: '', value: '', id: null, value_si: '', value_ta: '' };

  ird: IirdInfo = {
    commencementdate: '',
    bac: '',
    preferredlanguage: null,
    preferredmodeofcommunication: null,
    isboireg: false,
    boistartdate: '',
    boienddate: '',
    purposeofregistration: '',
    otherpurposeofregistration: '',
    isforiegncompany: false,
    dateofincorporationforeign: '',
    countryoforigin: '',
    parentcompanyexists: false,
    localparentcompany: '',
    parentcompanyreference: '',
    parentcompanyreferenceid: '',
    parentcompanyname: '',
    parentcompanyaddress: '',
    countryofincorporation: '',
    dateofincorporationparentcompany: '',
    fax: '',
    contactpersonname: '',
  };

  coreShareGroups: Array<IcoreShareGroup> = [];
  paymentSuccess = false;
  payConfirm = false;
  resubmitSuccess = false;
  addNewObjective = false;
  provinces: Array<IProvince> = [];
  districts: Array<IDistrict> = [];
  cities: Array<ICity> = [];
  gns: Array<IGnDivision> = [];

  screen1Provinces: Array<IProvince> = [];
  screen1Districts: Array<IDistrict> = [];
  screen1Cities: Array<ICity> = [];
  screen1Gns: Array<IGnDivision> = [];

  countries: Array<Icountry> = [];
  docList = [];
  uploadList = [];
  uploadedList = [];
  uploadedListTokens = [];
  uploadedDocsWithPages = [];

  stepOn = 0;

  payment: any = {};
  totalWithoutTaxVat = 0;
  incorporationPrice = 0;
  incorporationVat = 0;
  convenienceFee = 0;
  convenienceFeeValue = 0;
  incorporationOtherTaxes = 0;
  secPayment: any = [];

  totalPayment = 0;
  formTotalPrice = 0;
  copyTotalPrice = 0;


  blockPaymentStep = false;

  fileUploadError = '';

  loadNICstakeholders = false;

  progress = {

    stepArr: [
      { label: 'Company Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Stakeholders', icon: 'fa fa-users', status: '' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Inland Revenue', icon: 'fa fa-money-bill-alt', status: '' },
    ],

    progressPercentage: '7.14%'

  };


  coreShareHolderGroups = [
    { id: 1, name: 'Group1' }, { id: 2, name: 'Group2' }, { id: 3, name: 'Group3' }
  ];


  companyDocuments = [];

  // director interfaces
  directorList: IDirectors = { directors: [] };
  // tslint:disable-next-line:max-line-length
  director: IDirector = { id: 0, secRegDate: '', showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec: true };
  // sercretory interfaces
  secList: ISecretories = { secs: [] };
  secFirmList: ISecretories = { secs: [] };
  // tslint:disable-next-line:max-line-length
  sec: ISecretory = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', benifOwnerType : 'local', screen1Provinces: [], screen1Districts: [], screen1Cities: [], firm_city: '' , firm_district: '', firm_province: '', validateSecShBenifInEdit : false, secBenifList : { ben : [] }, forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '' , passport_issued_country: '' };
  // share holder interfaces
  shList: IShareHolders = { shs: [] };
  shFirmList: IShareHolders = { shs: [] };
  // tslint:disable-next-line:max-line-length
  public sh: IShareHolder = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , firm_city: '', firm_district: '', firm_province: '',  benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null }, passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '' };

  benifList: IShareHolderBenifList = { ben: [] };
  public sh_benif: IShareHolderBenif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] };

  secBenifList: IShareHolderBenifList = { ben: [] };
  public sec_sh_benif: IShareHolderBenif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '',  screen1Provinces: [], screen1Districts: [], screen1Cities: [] };


  directorToShareHolder = false;
  shareHolderToDirector = false;

  /**validation vars */

  step1ValidationStatus = false;
  enableStep1Submission = false;

  enableStep2Submission = false;
  enableStep2SubmissionEdit = true;
  step2SubmitMessage = '';
  step2SubmitStatus = false;
  enableStep2Next = false;

  directorValitionMessage = '';
  directorAlreadyExistMessage = '';
  secAlreadyExistMessage = '';
  shAlreadyExistMessage = '';

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

  isGuarantyCompany = false;
  isShareholderLable = 'Shareholder';

  step2ValidationStatus = false;

  secpayment = [];
  dirpayment = [];
  IShareHolderBenifList: any;

  valideIRD = false;
  submitIRDmessage = '';

   /***objectives */
   level1Objectives: Array<IObjective> = [];
   level2Objectives: Array<IObjective> = [];
   level3Objectives: Array<IObjective> = [];
   level4Objectives: Array<IObjective> = [];
   level5Objectives: Array<IObjective> = [];

   level2Objectives_Filterd: Array<IObjective> = [];
   level3Objectives_Filterd: Array<IObjective> = [];
   level4Objectives_Filterd: Array<IObjective> = [];
   level5Objectives_Filterd: Array<IObjective> = [];

   newObjective: IObjectiveRow = {objective1: '', objective2 : '', objective3: '',  objective4: '', objective5: '', level1Objectives : null, level2Objectives : null, level3Objectives : null, level4Objectives : null, level5Objectives : null };
   validNewObjective = false;
   companyObjectiveCollection: IObjectiveCollection = { collection: [] };

   foreignCompTypeMakePaymentSet = false;
   forgiegnSentApproval = false;


  getLevel2Filterd(level1Value, load = false ) {

     level1Value = level1Value ? level1Value.toString() : '';

    // tslint:disable-next-line:prefer-const
   let level2Objectives = Object.assign({}, this.level2Objectives);

   // tslint:disable-next-line:prefer-const
   let filterLevel2Objectives: Array<IObjective> = [];

   // tslint:disable-next-line:prefer-const
   for ( let i in level2Objectives ) {


       if ( level2Objectives[i].parent_id === level1Value ) {

        filterLevel2Objectives.push(  level2Objectives[i] );
       }
   }

    this.level2Objectives_Filterd = filterLevel2Objectives;
    if (load === false ) {
      this.newObjective.objective2 = '';
       this.newObjective.objective3 = '';
       this.newObjective.objective4 = '';
       this.newObjective.objective5 = '';
    }

    this.newObjectiveValidation();
  }

  getLevel2FilterdEdit(level1Value, load = false , i ) {

    // console.log(level1Value);

    level1Value = level1Value ? level1Value.toString() : '';

   // console.log(level1Value);

    // tslint:disable-next-line:prefer-const
   let level2Objectives = Object.assign({}, this.level2Objectives);

   // tslint:disable-next-line:prefer-const
   let filterLevel2Objectives: Array<IObjective> = [];

   // tslint:disable-next-line:prefer-const
   for ( let j in level2Objectives ) {

       if ( level2Objectives[j].parent_id === level1Value ) {

        filterLevel2Objectives.push(  level2Objectives[j] );
       }
   }

     this.companyObjectiveCollection.collection[i].level2Objectives = filterLevel2Objectives;


    if (load === false ) {
      this.companyObjectiveCollection.collection[i].objective2 = '';
      this.companyObjectiveCollection.collection[i].objective3 = '';
      this.companyObjectiveCollection.collection[i].objective4 = '';
      this.companyObjectiveCollection.collection[i].objective5 = '';
    }

    this.step1Validation();
  }
  getLevel3FilterdEdit(level2Value, load = false , i ) {

    level2Value = level2Value ? level2Value.toString() : '';

    // tslint:disable-next-line:prefer-const
   let level3Objectives = Object.assign({}, this.level3Objectives);

   // tslint:disable-next-line:prefer-const
   let filterLevel3Objectives: Array<IObjective> = [];

   // tslint:disable-next-line:prefer-const
   for ( let j in level3Objectives ) {

       if ( level3Objectives[j].parent_id === level2Value ) {

        filterLevel3Objectives.push(  level3Objectives[j] );
       }
   }

    this.companyObjectiveCollection.collection[i].level3Objectives = filterLevel3Objectives;
    if (load === false ) {
      this.companyObjectiveCollection.collection[i].objective3 = '';
      this.companyObjectiveCollection.collection[i].objective4 = '';
      this.companyObjectiveCollection.collection[i].objective5 = '';
    }

    this.step1Validation();
  }

  getLevel4FilterdEdit(level3Value, load = false , i ) {

    level3Value = level3Value ? level3Value.toString() : '';

    // tslint:disable-next-line:prefer-const
   let level4Objectives = Object.assign({}, this.level4Objectives);

   // tslint:disable-next-line:prefer-const
   let filterLevel4Objectives: Array<IObjective> = [];

   // tslint:disable-next-line:prefer-const
   for ( let j in level4Objectives ) {

       if ( level4Objectives[j].parent_id === level3Value ) {

        filterLevel4Objectives.push(  level4Objectives[j] );
       }
   }

    this.companyObjectiveCollection.collection[i].level4Objectives = filterLevel4Objectives;
    if (load === false ) {
      this.companyObjectiveCollection.collection[i].objective4 = '';
      this.companyObjectiveCollection.collection[i].objective5 = '';
    }

    this.step1Validation();
  }

  getLevel5FilterdEdit(level4Value, load = false , i ) {

    level4Value = level4Value ? level4Value.toString() : '';

    // tslint:disable-next-line:prefer-const
   let level5Objectives = Object.assign({}, this.level5Objectives);

   // tslint:disable-next-line:prefer-const
   let filterLevel5Objectives: Array<IObjective> = [];

   // tslint:disable-next-line:prefer-const
   for ( let j in level5Objectives ) {

       if ( level5Objectives[j].parent_id === level4Value ) {

        filterLevel5Objectives.push(  level5Objectives[j] );
       }
   }

    this.companyObjectiveCollection.collection[i].level5Objectives = filterLevel5Objectives;
    if (load === false ) {
      this.companyObjectiveCollection.collection[i].objective5 = '';
    }

    this.step1Validation();
  }




  getLevel3Filterd(level2Value, load = false ) {

    level2Value = level2Value ? level2Value.toString() : '';

    // tslint:disable-next-line:prefer-const
   let level3Objectives = Object.assign({}, this.level3Objectives);
   // tslint:disable-next-line:prefer-const
   let filterLevel3Objectives: Array<IObjective> = [];

   // tslint:disable-next-line:prefer-const
   for ( let i in level3Objectives ) {
       if ( level3Objectives[i].parent_id === level2Value.toString() ) {

        filterLevel3Objectives.push(  level3Objectives[i] );
       }
   }

    this.level3Objectives_Filterd = filterLevel3Objectives;

    if (load === false ) {
      this.newObjective.objective3 = '';
       this.newObjective.objective4 = '';
       this.newObjective.objective5 = '';
    }

    this.newObjectiveValidation();
  }

  getLevel4Filterd(level3Value, load = false ) {

    level3Value = level3Value ? level3Value.toString() : '';

    // tslint:disable-next-line:prefer-const
   let level4Objectives = Object.assign({}, this.level4Objectives);
   // tslint:disable-next-line:prefer-const
   let filterLevel4Objectives: Array<IObjective> = [];

   // tslint:disable-next-line:prefer-const
   for ( let i in level4Objectives ) {
       if ( level4Objectives[i].parent_id === level3Value.toString() ) {

        filterLevel4Objectives.push(  level4Objectives[i] );
       }
   }

    this.level4Objectives_Filterd = filterLevel4Objectives;

    if (load === false ) {
      this.newObjective.objective4 = '';
       this.newObjective.objective5 = '';
    }

    this.newObjectiveValidation();
  }

  getLevel5Filterd(level4Value, load = false ) {

    level4Value = level4Value ? level4Value.toString() : '';

    // tslint:disable-next-line:prefer-const
   let level5Objectives = Object.assign({}, this.level5Objectives);
   // tslint:disable-next-line:prefer-const
   let filterLevel5Objectives: Array<IObjective> = [];

   // tslint:disable-next-line:prefer-const
   for ( let i in level5Objectives ) {
       if ( level5Objectives[i].parent_id === level4Value.toString() ) {

        filterLevel5Objectives.push(  level5Objectives[i] );
       }
   }

    this.level5Objectives_Filterd = filterLevel5Objectives;

    if (load === false ) {
       this.newObjective.objective5 = '';
    }


    this.newObjectiveValidation();
  }

  newObjectiveValidation() {

     if ( this.newObjective.objective1
         // &&
         // this.newObjective.objective2 &&
        //  this.newObjective.objective3 &&
        //  this.newObjective.objective4 &&
        //  this.newObjective.objective5
          ) {
            this.validNewObjective = true;
         } else {

            this.validNewObjective = false;
         }
  }

  validateObjectiveList() {

     if ( !this.companyObjectiveCollection.collection.length ) {
       return false;
     }

     for ( let i in this.companyObjectiveCollection.collection ) {
         if ( !(
            this.companyObjectiveCollection.collection[i].objective1
            // &&
           // this.companyObjectiveCollection.collection[i].objective2 &&
           // this.companyObjectiveCollection.collection[i].objective3 &&
           // this.companyObjectiveCollection.collection[i].objective4 &&
           // this.companyObjectiveCollection.collection[i].objective5

          ) ){

            return false;
          }
     }

     return true;
  }

  createNewObjective() {
    this.addNewObjective = true;
  }
  closeNewObjectivePop() {
    this.addNewObjective = false;
  }
  addNewCompanyObjective() {
     let copy = Object.assign({}, this.newObjective);
     this.companyObjectiveCollection.collection.push(copy);

    this.newObjective = {objective1: '', objective2 : '', objective3: '',  objective4: '', objective5: '' };
    this.validNewObjective = false;
    this.addNewObjective = false; // close new objective window

    for (let i in this.companyObjectiveCollection.collection ) {

      this.getLevel2FilterdEdit(this.companyObjectiveCollection.collection[i].objective1, true , i);
      this.getLevel3FilterdEdit(this.companyObjectiveCollection.collection[i].objective2, true , i);
      this.getLevel4FilterdEdit(this.companyObjectiveCollection.collection[i].objective3, true , i);
      this.getLevel5FilterdEdit(this.companyObjectiveCollection.collection[i].objective4, true , i);
    }
  }

  removeFromObjectiveList(i){
    this.companyObjectiveCollection.collection.splice(i, 1);
    this.step1Validation();
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
      this.companyAddress.district = '';
      this.companyAddress.city = '';
      this.companyAddress.gn_division = '';
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
      this.companyAddress.city = '';
      this.companyAddress.gn_division = '';
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
      this.companyAddress.gn_division = '';
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
    if ( type === 'sec' ) {
        this.secList.secs[i].screen1Provinces = filterProvince;
        this.validateSecEdit(i);
    }
    if ( type === 'secFirm' ) {
      this.secFirmList.secs[i].screen1Provinces = filterProvince;
      this.validateSecEditForSecFirm(i);
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

   if ( type === 'director' ) {
    this.directorList.directors[i].screen1Districts = filterDistricts;

     if (load === false ) {
      this.directorList.directors[i].city = '';
      this.directorList.directors[i].district = '';
     }

    this.validateDirectorEdit(i);
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

    if ( type === 'secFirm' ) {
      this.secFirmList.secs[i].screen1Districts = filterDistricts;

      if (load === false ) {
        this.secFirmList.secs[i].firm_city = '';
        this.secFirmList.secs[i].firm_district = '';
       }
      this.validateSecEditForSecFirm(i);

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
     if ( type === 'director' ) {
      this.directorList.directors[i].screen1Cities = filterCities;
      if (load === false ) {
        this.directorList.directors[i].city = '';
       }
      this.validateDirectorEdit(i);
      }
      if ( type === 'sec' ) {
        this.secList.secs[i].screen1Cities = filterCities;
        if (load === false ) {
          this.secList.secs[i].city = '';
          this.secList.secs[i].firm_city = '';
         }
        this.validateSecEdit(i);
      }
      if ( type === 'secFirm' ) {
        this.secFirmList.secs[i].screen1Cities = filterCities;
        if (load === false ) {
          this.secFirmList.secs[i].firm_city = '';
         }
        this.validateSecEditForSecFirm(i);
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


  constructor(
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private iNcoreService: IncorporationService,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private general: GeneralService,
    private helper: HelperService,
    public pdc: DataService,
    private crToken: PaymentService,
    private iBy: IBuyDetails
    ) {
    this.companyInfo.email = '';
   // this.companyInfo.objective = '';

    this.companyId = route.snapshot.paramMap.get('companyId');
    this.loginUserEmail = localStorage.getItem('currentUser');

    this.loadHeavyData();

    // tslint:disable-next-line:prefer-const
   // for (let i in this.payment) {
      // console.log(this.payment[i]);
  //    this.totalPayment = this.totalPayment + parseFloat(this.payment[i]['val']) * parseFloat(this.payment[i]['copies']);
      // console.log(this.totalPayment);
   // }
  //  this.totalPayment = this.totalPayment + this.incorporationPrice;
  //  this.totalWithoutTaxVat = this.totalPayment;
   // this.totalPayment = ( this.totalPayment * this.incorporationVat ) / 100 + this.totalPayment ;

  }

  sanitize(url: string) {
    return this.sanitizer.bypassSecurityTrustUrl(url);
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

  private get_company_payment_type_for_company_type() {

      // tslint:disable-next-line:prefer-const
      let types = {
        'COMPANY_TYPE_PRIVATE' : 'PAYMENT_PRIVATE_COMPANY_REGISTRATION',
        'COMPANY_TYPE_PUBLIC' : 'PAYMENT_PUBLIC_COMPANY_REGISTRATION',
        'COMPANY_TYPE_GUARANTEE_32' : 'PAYMENT_GURANTEE_COMPANY_REGISTRATION',
        'COMPANY_TYPE_GUARANTEE_34' : 'PAYMENT_GURANTEE_COMPANY_REGISTRATION',
        'COMPANY_TYPE_OVERSEAS' : 'PAYMENT_OVERSEAS_COMPANY_REGISTRATION',
        'COMPANY_TYPE_OFFSHORE' : 'PAYMENT_OFFSHORE_COMPANY_REGISTRATION',
        'COMPANY_TYPE_UNLIMITED' : 'PAYMENT_UNLIMITED_COMPANY_REGISTRATION',

      };
      return types[this.compayType.key];

  }

  calculatePayment() {
    this.totalPayment = 0;
    this.formTotalPrice = 0;
    this.copyTotalPrice = 0;
    this.paymentItems = [];
    let totalCopies = 0;

    // tslint:disable-next-line:prefer-const
    for (let i in this.payment) {

      let description = '';

      if (this.payment[i]['member_name'] ) {
          description = this.payment[i]['document_name'] + ' For ' + this.payment[i]['member_name'];
      }
      else if (this.payment[i]['changed_name'] ) {
        description = this.payment[i]['document_name'] + ' for Changed Name ' + this.payment[i]['changed_name'];
      }  else {
        description =  this.payment[i]['document_name'];
      }

      this.totalPayment = this.totalPayment + this.payment[i]['val'] * this.payment[i]['copies'];
      this.copyTotalPrice = this.copyTotalPrice + this.payment[i]['val'] * this.payment[i]['copies'];

      this.paymentItems.push(
        {
            fee_type: 'PAYMENT_ISSUING_CERTIFIED_COPY_OF_FORM',
            description: description ,
            quantity: this.payment[i]['copies'],
        }
      );
      // tslint:disable-next-line:radix
      totalCopies = totalCopies + parseInt(this.payment[i]['copies']);

    }
    if (totalCopies) {
      this.requesetCopies = true;
    } else {
      this.requesetCopies = false;
    }


    this.totalWithoutTaxVat = this.totalPayment;
    this.totalPayment = ( (this.totalPayment * this.incorporationVat) / 100 ) + this.totalPayment ;
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

    $('button.add-share').on('click', function () {
      $('#share-modal .close-modal-item').trigger('click');
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

    this.spinner.show();

  }

  changeProgressStatuses(newStatus = 0, blockStep = false) {

    // tslint:disable-next-line:prefer-const
    let stopProcess = false;
    this.blockPaymentStep = false;

    if (blockStep === true && this.processStatus !== 'COMPANY_STATUS_REQUEST_TO_RESUBMIT') {

    //  this.blockPaymentStep = true;
   //   return false;

    }

    /**security checkpoint - check company status */
   /* if ( newStatus <= 3 ) {

      if (
        !(
        this.processStatus === 'COMPANY_NAME_APPROVED' ||
        this.processStatus === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
        (this.processStatus === 'COMPANY_FOREIGN_STATUS_PAYMENT_PENDING' && (this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS'))
        )
      ) {
        this.spinner.hide();
        this.router.navigate(['/dashboard/home']);
        return false;
      }

    }*/
    /**security checkpoint - check company status */

  /*  if ( newStatus >= 2  && this.step2ValidationStatus ) {
      this.changeProgressStatuses(1, false);
      alert('Cheatin uh? Anyway Please create your stakeholders as properly');
      return false;
    }

    if ( newStatus >= 4 &&  this.processStatus !== 'COMPANY_FOREIGN_STATUS_PAYMENT_PENDING' && !this.validateUploadeStatusFlag ) {

      this.changeProgressStatuses(3, false);
      alert('Cheatin uh? Anyway Please upload all documents');
      this.spinner.hide();
      return false;
    }*/

    this.stepOn = newStatus;
    this.progress.progressPercentage = (this.stepOn >= 6) ? (7.14 * 2 + this.stepOn * 14.28) + '%' : (7.14 + this.stepOn * 14.28) + '%';

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

  enablePay() {
    this.changeProgressStatuses(4, false);
  }
  blockPay() {
    this.changeProgressStatuses(3, false);
  }

  private loadHeavyData() {
    const data = {
      companyId: this.companyId,
    };
    this.spinner.show();

    // load Company data from the server
    this.iNcoreService.certifiedCopiesHeavyData(data)
      .subscribe(
        req => {

          this.provinces = req['data']['pdc']['provinces'];
          this.districts = req['data']['pdc']['districts'];
          this.cities = req['data']['pdc']['cities'];
          this.gns = req['data']['pdc']['gns'];
          this.level1Objectives = req['data']['objectives']['level1'];
          this.level2Objectives = req['data']['objectives']['level2'];
          this.level3Objectives = req['data']['objectives']['level3'];
          this.level4Objectives = req['data']['objectives']['level4'];
          this.level5Objectives = req['data']['objectives']['level5'];

          this.loadData();

         // this.spinner.hide();
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
    this.iNcoreService.certifiedCopyData(data)
      .subscribe(
        req => {
          this.compayType = req['data']['companyType'];
          this.externalGlobComment = req['data']['external_global_comment'];
          this.processStatus = req['data']['processStatus'];

          if ( !( this.processStatus === 'COMPANY_STATUS_APPROVED' || this.processStatus === 'COMPANY_FOREIGN_STATUS_APPROVED' || this.processStatus === 'COMPANY_NAME_CHANGE_APPROVED') ) {
            this.router.navigate(['/dashboard/home']);
            return false;
          }


          this.payment = req['data']['payment_new'];
          this.incorporationPrice = req['data']['incorporationPrice'];
          this.incorporationVat = req['data']['incorporationVat'];
          this.convenienceFee = req['data']['incorporationConvenienceFee'];
          this.convenienceFeeValue = this.convenienceFee / 100;
          this.incorporationOtherTaxes = req['data']['incorporationOtherTaxes'];
        //  this.companyTypes = Array.of(req['data']['compnayTypes']);
          this.postFixEn = req['data']['postfix'];
          this.postFixSi = req['data']['postfix_si'];
          this.postFixTa = req['data']['postfix_ta'];

        //  this.companyObjectives = Array.of(req['data']['companyObjectives']);
          this.companyAddress = req['data']['companyAddress'];
          this.step2ValidationStatus = req['data']['stakehodlerE'];
          if (req['data']['companyForAddress']) {
            this.companyForAddress = req['data']['companyForAddress'];
          }
          this.companyInfo = req['data']['companyInfo'];
          this.loginUserInfo = req['data']['loginUser'];
          this.loginUserAddress = req['data']['loginUserAddress'];

          this.docList = req['data']['docList'];
          this.uploadList = req['data']['uploadList'];

          if (
            req['data']['uploadList']['director'].length ||
            req['data']['uploadList']['secFirm'].length ||
            req['data']['uploadList']['sec'].length ||
            req['data']['uploadList']['other'].length ||
            req['data']['uploadList']['name_change'].length ||
            req['data']['uploadList']['form_10'].length) {
                 this.hasCopyToRequest = true;
           } else {
                 this.hasCopyToRequest = false;
           }


          this.uploadedList = req['data']['uploadedDocs'];
          this.uploadedListTokens = req['data']['uploadedDocsWithData'];
          this.uploadedDocsWithPages = req['data']['uploadedDocsWithPages'];

          // change the process steps
          this.progress.stepArr = [
            { label: 'Company Details', icon: 'fa fa-list-ol', status: 'active' },
            { label: 'Stakeholders', icon: 'fa fa-users', status: '' },
            { label: 'Download Documents', icon: 'fa fa-download', status: '' },
            { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
          //  { label: 'Inland Revenue', icon: 'fas fa-building', status: '' },
          //  { label: 'Labour Department', icon: 'fas fa-university', status: '' },
          ];
          this.progress.stepArr.push({ label: 'Inland Revenue', icon: 'fas fa-building', status: ''});
          this.progress.stepArr.push({ label: 'Labour Department', icon: 'fas fa-university', status: ''});

          this.countries = req['data']['countries'];

          this.coreShareGroups = req['data']['coreShareGroups'];

          // this.setisRegForPrivateCompany();

          this.isGuarantyCompany =  ( this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34' );
          this.isShareholderLable = (this.isGuarantyCompany) ? 'Member' : 'Shareholder';

          this.secList.secs = req['data']['secs'];
          this.secFirmList.secs = req['data']['secs_firms'];
          // tslint:disable-next-line:prefer-const
          for (let i in this.secFirmList.secs) {

            this.getProvincesForStakeHolderEdit('secFirm', i);
            this.getDistrictsForStakeholderEdit('secFirm', i , this.secFirmList.secs[i].firm_province, true );
            this.getCitiesForStakeholderEdit( 'secFirm', i , this.secFirmList.secs[i].firm_district, true  );
            this.secFirmList.secs[i].benifOwnerType = 'local';
            this.validateSecEditForSecFirm(i);
          }

          // tslint:disable-next-line:prefer-const
          for (let i in this.secList.secs) {

            this.getProvincesForStakeHolderEdit('sec', i);
            this.getDistrictsForStakeholderEdit('sec', i , this.secList.secs[i].province, true );
            this.getCitiesForStakeholderEdit( 'sec', i , this.secList.secs[i].district, true  );
            this.validateSecEdit(i);

           // this.getProvincesForBenEdit('sec_sh_benif', i );
           // this.getDistrictsForBenEdit('sec_sh_benif', i , this.sec_sh_benif.province );
          //  this.getCitiesForBenEdit( 'sec_sh_benif', i , this.sec_sh_benif.district );
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
            this.validateShareHolderEdit(i);
          }

          this.directorList.directors = req['data']['directors'];
          // tslint:disable-next-line:prefer-const
          for (let i in this.directorList.directors) {

            this.getProvincesForStakeHolderEdit('director', i);
            this.getDistrictsForStakeholderEdit('director', i , this.directorList.directors[i].province, true );
            this.getCitiesForStakeholderEdit( 'director', i , this.directorList.directors[i].district, true  );

            this.validateDirectorEdit(i);
          }

          this.enableStep2Next = req['data']['enableStep2Next'];
          this.companyDocuments = req['data']['documents'];
          this.step1Validation();
          this.step2Validation();

          // screen1 addresses
          this.getProvincesForScreen1();
          this.getDistrictsForScreen1(this.companyAddress.province, true);
          this.getCitiesForScreen1(this.companyAddress.district, true);
          this.getGnsForScreen1(this.companyAddress.city, true);

          // screen1 objective
         // this.getLevel2Filterd( (this.companyInfo.objective1) ? this.companyInfo.objective1.toString() : ''.toString(), true);
         // this.getLevel3Filterd((this.companyInfo.objective2) ? this.companyInfo.objective2.toString() : ''.toString(), true);
         // this.getLevel4Filterd((this.companyInfo.objective3) ? this.companyInfo.objective3.toString() : ''.toString(), true);
        //  this.getLevel5Filterd((this.companyInfo.objective4) ? this.companyInfo.objective4.toString() : ''.toString(), true);

           let companyObjectivesListCount = req['data']['companyObjectiveListCount'];
           let companyObjectivesList = req['data']['companyObjectiveList'];

           this.companyObjectiveCollection.collection = [];

           if (companyObjectivesListCount) {
               for ( let i in companyObjectivesList ) {

                let objItem: IObjectiveRow = {
                     objective1 :  companyObjectivesList[i]['objective1'],
                     objective2 : companyObjectivesList[i]['objective2'] ,
                     objective3 : companyObjectivesList[i]['objective3'],
                     objective4 : companyObjectivesList[i]['objective4'],
                     objective5 : companyObjectivesList[i]['objective5'],
                     level1Objectives : this.level1Objectives,
                     level2Objectives: this.level2Objectives,
                     level3Objectives: this.level3Objectives,
                     level4Objectives: this.level4Objectives,
                     level5Objectives: this.level5Objectives

                };
                this.companyObjectiveCollection.collection.push(objItem);

               }

               for (let i in this.companyObjectiveCollection.collection ) {

                this.getLevel2FilterdEdit(this.companyObjectiveCollection.collection[i].objective1.toString(), true , i);
                this.getLevel3FilterdEdit( ( this.companyObjectiveCollection.collection[i].objective2) ? this.companyObjectiveCollection.collection[i].objective2.toString() : '' , true , i);
                this.getLevel4FilterdEdit(( this.companyObjectiveCollection.collection[i].objective3) ? this.companyObjectiveCollection.collection[i].objective3.toString() : '', true , i);
                this.getLevel5FilterdEdit(( this.companyObjectiveCollection.collection[i].objective4) ? this.companyObjectiveCollection.collection[i].objective4.toString() : '', true , i);
              }
           }
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

          // this.changeProgressStatuses(this.stepOn);

          this.calculatePayment();

          this.changeProgressStatuses(6);


          this.spinner.hide();
        }
      );



  }

  foreignGoToPay() {
    this.changeProgressStatuses(6);
    this.foreignCompTypeMakePaymentSet = false;
  }

  closeApprovalPop() {
    this.forgiegnSentApproval = false;
    this.router.navigate(['/dashboard/home']);
  }

  makeApproveForiegn(){

    this.spinner.show();

    if ( !this.validateUploadeStatusFlag) {
      this.changeProgressStatuses(3, false);
      alert('Cheatin uh? Anyway Please upload all documents');
      this.spinner.hide();
    }

    const data = {
      company_id: this.companyId
    };

    this.iNcoreService.incorpForgeignRequestApproval(data)
      .subscribe(
        req => {
          this.spinner.hide();
          this.resubmitSuccess = true;

         // this.loadData();
        //   this.router.navigate(['/dashboard/home']);
         // this.changeProgressStatuses( 5 , false );

         this.forgiegnSentApproval = true;

        },
        error => {
          this.spinner.hide();
          this.resubmitSuccess = false;
        }

      );
  }

  submitStep1() {

    /* if (this.processStatus === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT') {
      this.changeProgressStatuses(1);
      this.stepOn = 1;
      return true;
    } */

    const data = {
      companyId: this.companyId,
      companyType: this.companyInfo['type_id'],
      address1: this.companyAddress['address1'],
      address2: this.companyAddress['address2'],
      gn_division: this.companyAddress['gn_division'],
      city: this.companyAddress['city'],
      district: this.companyAddress['district'],
      province: this.companyAddress['province'],
      postcode: this.companyAddress['postcode'],
      email: this.companyInfo['email'],
      objective1: this.companyInfo['objective1'],
      objective2: this.companyInfo['objective2'],
      objective3: this.companyInfo['objective3'],
      objective4: this.companyInfo['objective4'],
      objective5: this.companyInfo['objective5'],
      objectiveOther: this.companyInfo['otherObjective'],

      objective_array: this.companyObjectiveCollection,

      forAddress1: (this.companyForAddress['address1']) ? this.companyForAddress['address1'] : '',
      forAddress2: (this.companyForAddress['address2']) ? this.companyForAddress['address2'] : '',
      forCity: (this.companyForAddress['city']) ? this.companyForAddress['city'] : '',
      forProvince: (this.companyForAddress['province']) ? this.companyForAddress['province'] : '',
      forCountry: (this.companyForAddress['country']) ? this.companyForAddress['country'] : '',
      forPostcode: (this.companyForAddress['postcode']) ? this.companyForAddress['postcode'] : ''
    };

    this.iNcoreService.incorporationDataStep1Submit(data)
      .subscribe(
        req => {
          this.loadData();
          this.changeProgressStatuses(1);
        },
        error => {
          console.log(error);
        }

      );


  }

  changeDefaultStatus() {

    // tslint:disable-next-line:max-line-length
    this.director = { id: 0, secRegDate: '', showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec : true };
    // tslint:disable-next-line:max-line-length
    this.sec  = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', benifOwnerType : 'local', screen1Provinces: [], screen1Districts: [], screen1Cities: [], firm_city: '' , firm_district: '', firm_province: '', validateSecShBenifInEdit : false, secBenifList : { ben : [] } };
    // tslint:disable-next-line:max-line-length
    this.sh = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural', screen1Provinces: [], screen1Districts: [], screen1Cities: [] , firm_city: '', firm_district: '', firm_province: '',  benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '' , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '' };
    this.sh_benif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] };
    this.sec_sh_benif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '',  screen1Provinces: [], screen1Districts: [], screen1Cities: [] };

    this.director.type = 'local';
    this.sec.type = 'local';
    this.sh.type = 'local';
    this.loadNICstakeholders = false;
    this.directorNicLoaded = false;
    this.secNicLoaded = false;
    this.shNicLoaded = false;

    this.getProvincesForStakeHolder('director');
    this.getProvincesForStakeHolder('sec');
    this.getProvincesForStakeHolder('sh');

    this.getProvincesForBen('sec_sh_benif');

    this.getProvincesForBen('sh_benif');

    this.guarantee_sec_err_happend = false;

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
    this.step2Validation();
    this.validDirector = false;
    this.submitStakeholers('remove');

  }
  resetDirRecord() {
    // tslint:disable-next-line:prefer-const
    let conf = confirm('Are you sure you want to reset ?');

    if (!conf) {
      return true;
    }
    // tslint:disable-next-line:max-line-length
    this.director = { id: 0, secRegDate: '' , showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: null, noOfSingleShares: null, coreGroupSelected: null, coreShareGroupName: '', coreShareValue: null, screen1Districts: [], screen1Cities: [] , passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec: true};
    this.guarantee_sec_err_happend = false;
    this.loadNICstakeholders = false;
  }


  removeDirectorRecord(i: number, userId: number = 0) {
    this.directorList.directors.splice(i, 1);
    if (!userId) {

      return true;
    }
    const data = {
      userId: userId,
      companyId: this.companyId
    };
    this.iNcoreService.incorporationDeleteStakeholder(data)
      .subscribe(
        req => {
          this.spinner.hide();
          this.step2Validation();
          this.loadData();

        },
        error => {
          this.spinner.hide();
          console.log(error);
          alert(error);

        }

      );


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
    this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '' };
    this.step2Validation();
    this.validSec = false;
    this.submitStakeholers('remove');

  }

  resetSecRecord() {
    // tslint:disable-next-line:prefer-const
    let conf = confirm('Are you sure you want to reset ?');

    if (!conf) {
      return true;
    }
    // tslint:disable-next-line:max-line-length
   // this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', isShareholder: false, shareType: null, noOfSingleShares: null, coreGroupSelected: null, coreShareGroupName: '', coreShareValue: null, secType: null, secCompanyFirmId: '', pvNumber: '', firm_name: '', firm_province: null, firm_district: null, firm_city: null, firm_localAddress1: null, firm_localAddress2: null, firm_postcode: null, };
    // tslint:disable-next-line:max-line-length
    this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', isShareholder: false, shareType: null, noOfSingleShares: null, coreGroupSelected: null, coreShareGroupName: '', coreShareValue: null, secCompanyFirmId: '', pvNumber: '', firm_name: '', firm_province: null, firm_district: null, firm_city: null, firm_localAddress1: null, firm_localAddress2: null, firm_postcode: null, benifOwnerType : 'local', screen1Districts: [], screen1Cities: [], validateSecShBenifInEdit : false, secBenifList : { ben : [] } , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '' };
    this.secBenifList = { ben: [] };
    this.loadNICstakeholders = false;
    this.guarantee_sec_err_happend = false;

  }

  removeSecRecord(i: number, userId: number = 0) {

    this.secList.secs.splice(i, 1);
    if (!userId) {
      return true;
    }
    const data = {
      userId: userId,
      companyId: this.companyId
    };

    this.iNcoreService.incorporationDeleteStakeholder(data)
      .subscribe(
        req => {
          this.spinner.hide();
          this.step2Validation();
          this.loadData();
        },
        error => {
          this.spinner.hide();
          console.log(error);
          alert(error);
        }

      );

  }

  removeSecFirmShFirmRecord(userId) {
    const data = {
      companyId: this.companyId,
      userId: userId,

    };
    this.spinner.show();
    this.iNcoreService.incorpShFirmForSecFirmRemove(data)
      .subscribe(
        req => {
          this.spinner.hide();
          this.loadData();
          this.changeProgressStatuses(1);

        },
        error => {
          this.spinner.hide();
          console.log(error);
        }

      );
  }

  removeShFirmRecord(i: number, firmId = 0) {

    this.shFirmList.shs.splice(i, 1);
    if (!firmId) {
      return true;
    }
    const data = {
      userId: firmId,
      companyId: this.companyId
    };

    this.iNcoreService.incorpShFirmRemove(data)
      .subscribe(
        req => {
          this.spinner.hide();
          this.step2Validation();
          this.loadData();
        },
        error => {
          this.spinner.hide();
          console.log(error);
          alert(error);
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
    this.sh = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural',  screen1Districts: [], screen1Cities: [], benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '' , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: ''};

    this.step2Validation();
    this.validSh = false;
    this.submitStakeholers('remove');
    this.benifList.ben = [];
  }
  resetShRecord() {
    // tslint:disable-next-line:prefer-const
    let conf = confirm('Are you sure you want to reset ?');

    if (!conf) {
      return true;
    }
    // tslint:disable-next-line:max-line-length
    this.sh = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', screen1Districts: [], screen1Cities: [], noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural', benifOwnerType: 'local', pvNumber: '', firm_name: '', firm_province: '', firm_district: '', firm_city: '', firm_localAddress1: '', firm_localAddress2: '', firm_postcode: '', firm_email: '', firm_mobile: '', firm_phone: '', coreGroupSelected: null, coreShareGroupName: '' , validateAddBenif: false,  shareRow: { name: '', no_of_shares: null, type: null,   }, passport_issued_country: '' , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '' };

  this.benifList = { ben: [] };
  this.sh_benif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '',   screen1Districts: [], screen1Cities: []};
  this.loadNICstakeholders = false;

  }

  removeSecFirmRecord(i: number, userId: number = 0) {
    this.shList.shs.splice(i, 1);
    if (!userId) {

      return true;
    }
    const data = {
      firmId: userId,
      companyId: this.companyId
    };
    this.iNcoreService.incorpSecFirmRemove(data)
      .subscribe(
        req => {
          this.spinner.hide();
          this.step2Validation();
          this.loadData();
        },
        error => {
          this.spinner.hide();
          console.log(error);
          alert(error);

        }

      );

  }

  removeShRecord(i: number, userId: number = 0) {
    this.shList.shs.splice(i, 1);
    if (!userId) {

      return true;
    }
    const data = {
      userId: userId,
      companyId: this.companyId
    };
    this.iNcoreService.incorporationDeleteStakeholder(data)
      .subscribe(
        req => {
          this.spinner.hide();
          this.step2Validation();
          this.loadData();
        },
        error => {
          this.spinner.hide();
          console.log(error);
          alert(error);

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


  fileChange(event, dbid , fileNane, fileUser = null, multipleRow = null , docStatus = 'DOCUMENT_PENDING', isFirm = 'no' ) {

    // console.log(event);
    //  console.log(fileNane);
    // console.log(fileUser);

    // tslint:disable-next-line:radix
    if (fileUser && parseInt(fileUser)) {
      // tslint:disable-next-line:radix
      fileUser = parseInt(fileUser);
    } else {
      fileUser = '';
    }
    if ( multipleRow === null ) {
      multipleRow = -1;
   } else{
      // tslint:disable-next-line:radix
      multipleRow = parseInt(multipleRow);
    }

    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      // tslint:disable-next-line:prefer-const
      let file: File = fileList[0];

      // tslint:disable-next-line:prefer-const
      let fileSize = fileList[0].size;

      if (fileSize > 1024 * 1024 * 4) { // 4mb restriction
        alert('File size should be less than 4 MB');
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile', file, file.name);
      formData.append('fileName', this.slugify(fileNane) + '-' + this.slugify(fileUser));
      formData.append('fileType', fileNane);
      formData.append('companyId', this.companyId);
      formData.append('userId', fileUser);
      formData.append('multipleId', multipleRow);
      formData.append('isFirm', isFirm );
      formData.append('docStatus', docStatus);
      formData.append('file_type_id', dbid);

      // let headers = new Headers();
      /** In Angular 5, including the header Content-Type can invalidate your request */

      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');


      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.getFileUploadURL();

      this.calculatePayment();

      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            //  console.log(data);

            if ( data['error'] === 'yes' ) {
              this.fileUploadError = data['message'];
              this.spinner.hide();
              alert(data['message']);
              return false;
            }
            this.fileUploadError = '';

            const datas = {
              companyId: this.companyId,
              directors: this.directorList,
              secretories: this.secList,
              shareholders: this.shList

            };

            this.iNcoreService.incorporationDataStep2Submit(datas)
              .subscribe(
                req => {
                  this.spinner.hide();
                  //  console.log(req);

                    this.docList = req['data']['docList'];
                    this.uploadList = req['data']['uploadList'];
                    this.uploadedList = req['data']['uploadedList'];

                    if (this.validateUploadeStatus(req['data']['uploadList'], req['data']['uploadedList'])) {
                      this.validateUploadeStatusFlag = true;
                    } else {
                      this.validateUploadeStatusFlag = false;
                    }
                    this.loadData();
                    this.changeProgressStatuses(3);

                },
                error => {
                  this.spinner.hide();
                  this.changeProgressStatuses(3);
                }

              );



          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }

  }

  goToIRD() {
    this.changeProgressStatuses(5, false );
    this.paymentSuccess = false;
  }

  payAction() {
    this.loadData();
    this.router.navigate(['/dashboard/home']);
  }

  confirmPay() {

    let copyRecords = [];
    for (let i in this.payment) {

      copyRecords.push(
        {
            row_id : this.payment[i]['row_id'],
            copies : this.payment[i]['copies']
        }

     );

    }

    this.spinner.show();

    const data = {
      copiesArr: copyRecords,
      companyId: this.companyId,
      loginUser: this.loginUserEmail

    };

    this.iNcoreService.incorporationSavePubDocCopies(data)
      .subscribe(
        res => {

          if (res['status']) {

            this.spinner.hide();
            const buy: IBuy = {
              module_type: 'MODULE_PUBLIC_REQUEST_DOCUMENTS',
              module_id: res['req_id'],
              description:  'Certified Copies',
              item: this.paymentItems,
              extraPay: null,
              delevery_option: this.delevery_option
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

          } else {
            this.spinner.hide();
            alert('Your Request Failed');
            return false;
          }

        },
        error => {
          this.spinner.hide();
          console.log(error);
          alert(error);
        }

      );

  }

  pay() {

    this.spinner.show();

    const data = {
      company_id: this.companyId
    };

    if ( !this.validateUploadeStatusFlag) {

      this.changeProgressStatuses(3, false);
      alert('Cheatin uh? Anyway Please upload all documents');
      this.spinner.hide();
      return false;
    }


    this.iNcoreService.incorpPay(data)
      .subscribe(
        req => {
          // console.log(req);
          this.spinner.hide();
          this.paymentSuccess = true;
          this.loadData();

        },
        error => {
          console.log(error);
          this.spinner.hide();
          this.paymentSuccess = false;
        }

      );
  }

  resubmit() {

    this.spinner.show();

    if ( !this.validateUploadeStatusFlag) {
      this.changeProgressStatuses(3, false);
      alert('Cheatin uh? Anyway Please upload all documents');
      this.spinner.hide();
    }

    const data = {
      company_id: this.companyId
    };

    this.iNcoreService.incorpResubmit(data)
      .subscribe(
        req => {
          this.spinner.hide();
          this.resubmitSuccess = true;

          this.loadData();
           this.router.navigate(['/dashboard/home']);
         // this.changeProgressStatuses( 5 , false );

        },
        error => {
          this.spinner.hide();
          this.resubmitSuccess = false;
        }

      );
  }

  removeDirSec(userId) {

    const data = {
      companyId: this.companyId,
      userId: userId,

    };
    this.spinner.show();
    this.iNcoreService.incorpSecForDirRemove(data)
      .subscribe(
        req => {
          this.spinner.hide();
          this.loadData();
          this.changeProgressStatuses(1);

        },
        error => {
          this.spinner.hide();
          console.log(error);
        }

      );

  }

  removeDirSh(userId) {
    const data = {
      companyId: this.companyId,
      userId: userId,

    };
    this.spinner.show();
    this.iNcoreService.incorpShForDirRemove(data)
      .subscribe(
        req => {
          this.spinner.hide();
          this.loadData();
          this.changeProgressStatuses(1);

        },
        error => {
          this.spinner.hide();
          console.log(error);
        }

      );
  }

  removeSecSh(userId) {
    const data = {
      companyId: this.companyId,
      userId: userId,

    };
    this.spinner.show();
    this.iNcoreService.incorpShForSecRemove(data)
      .subscribe(
        req => {
          this.spinner.hide();
          this.loadData();
          this.changeProgressStatuses(1);

        },
        error => {
          this.spinner.hide();
          console.log(error);
        }

      );
  }

  validateSecEditForSecFirm(rowId) {
    // tslint:disable-next-line:prefer-const
    let secRow = this.secFirmList.secs[rowId];


    if (!(

      ((this.compayType.value === 'Public') ? secRow.pvNumber : true) &&
      secRow.firm_name &&
      secRow.firm_province &&
      secRow.firm_district &&
      secRow.firm_city &&
      secRow.firm_localAddress1 &&
      secRow.firm_postcode &&
      (secRow.firm_mobile && this.phonenumber(secRow.firm_mobile)) &&
      (secRow.firm_email && this.validateEmail(secRow.firm_email)

        &&
        (secRow.isShareholderEdit) ?
        (
          (secRow.shareTypeEdit === 'single' && secRow.noOfSingleSharesEdit ||
            secRow.shareTypeEdit === 'core' && secRow.coreGroupSelectedEdit ||
            secRow.shareTypeEdit === 'core' && (secRow.coreShareGroupNameEdit && secRow.coreShareValueEdit))
          &&
          secRow.secBenifList.ben.length
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

  setisRegForPrivateCompany() {

    if ( this.compayType.value === 'Private' ) {

       this.sec.isReg = true;

       // loop though edit modes
       if ( this.secList.secs.length ) {
         // tslint:disable-next-line:prefer-const
         for ( let i in this.secList.secs ) {

          // tslint:disable-next-line:prefer-const
          let secRow = this.secList.secs[i];
          secRow.isReg = true;

         }
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
      secRow.firstname &&
      secRow.lastname &&
      ( secRow.type === 'local' ? secRow.province : true ) &&
      ( secRow.type === 'local' ? secRow.district : true ) &&
      ( secRow.type === 'local' ? secRow.city : true ) &&
      ( secRow.type === 'local' ? secRow.postcode : true ) &&
      ( secRow.type === 'foreign' ? secRow.forProvince : true ) &&
      ( secRow.type === 'foreign' ? secRow.forCity : true ) &&
      ( secRow.type === 'foreign' ? ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS')  ? true :   secRow.forPostcode ) : true ) &&
      secRow.mobile && this.phonenumber(secRow.mobile, secRow.type ) &&
      secRow.email && this.validateEmail(secRow.email) &&
      ( secRow.type === 'local' ? secRow.localAddress1 : true ) &&
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

  validateRegCheckEdit($e, rowId) {
    // tslint:disable-next-line:prefer-const
    let secRow = this.secList.secs[rowId];

    secRow.isReg = $e ? true : false;
    this.validateSecEdit(rowId);

  }
  validateDirectorEdit(rowId) {

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
        (((this.compayType.key === 'COMPANY_TYPE_PUBLIC' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34') && directorRow.isSecEdit ) ? directorRow.secRegDate : true ) &&
        ((directorRow.isShareholderEdit === undefined || directorRow.isShareholderEdit === false) || directorRow.shareTypeEdit === 'single' && directorRow.noOfSingleSharesEdit ||
          directorRow.shareTypeEdit === 'core' && directorRow.coreGroupSelectedEdit ||
          directorRow.shareTypeEdit === 'core' && (directorRow.coreShareGroupNameEdit && directorRow.coreShareValueEdit)

        )
      )
      ) {
        //  this.directorValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.enableStep2Submission = false;
        this.enableStep2SubmissionEdit = false;
        return false;
      } else {

        // this.directorValitionMessage = '';
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

        (this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ?
         ( directorRow.province && directorRow.district && directorRow.city && directorRow.localAddress1 &&  directorRow.postcode ) : true &&

        directorRow.forCity &&
        directorRow.country &&
        directorRow.mobile && this.phonenumber(directorRow.mobile, directorRow.type) &&
        directorRow.forAddress1 &&
        ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS')  ? true :  directorRow.forPostcode ) &&
        directorRow.date &&
        ((directorRow.isShareholderEdit === undefined || directorRow.isShareholderEdit === false) || directorRow.shareTypeEdit === 'single' && directorRow.noOfSingleSharesEdit ||
          directorRow.shareTypeEdit === 'core' && directorRow.coreGroupSelectedEdit ||
          directorRow.shareTypeEdit === 'core' && (directorRow.coreShareGroupNameEdit && directorRow.coreShareValueEdit)

        )
      )) {

        // this.directorValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.enableStep2Submission = false;
        this.enableStep2SubmissionEdit = false;
        return false;

      } else {
        // this.directorValitionMessage = '';
        this.enableStep2Submission = true;
        this.enableStep2SubmissionEdit = true;
        return true;
      }
    }

  }


  validateOppDate(type = 'add', stakeholder = 'director', rowId = 0) {

  // return false;

   /* let date;
    if (type === 'add') {

      date = (stakeholder === 'sh') ? this.sh.date : (stakeholder === 'sec') ? this.sec.date : this.director.date;

    } else if (type === 'edit' && rowId >= 0) {

      date = (stakeholder === 'sh') ? this.shList.shs[rowId].date : (stakeholder === 'sec') ? this.secList.secs[rowId].date : this.directorList.directors[rowId].date;

    } else {
      alert('Something went wrong.');
      return false;
    }

    if (!date) {
      return true;
    }

    // tslint:disable-next-line:prefer-const
    let sendDate: Date = new Date(Date.parse(date.replace(/-/g, ' ')));
    // tslint:disable-next-line:prefer-const
    let today = new Date();
    today.setHours(0, 0, 0, 0);
    if (sendDate > today) {
      alert('The  appointment can\'t be in the future. Please pick another date.');

      if (type === 'add') {

        if (stakeholder === 'sh') { this.sh.date = null; }
        if (stakeholder === 'sec') { this.sec.date = null; }
        if (stakeholder === 'director') { this.director.date = null; }

      }
      if (type === 'edit') {
        if (stakeholder === 'sh') { this.shList.shs[rowId].date = null; }
        if (stakeholder === 'sec') { this.secList.secs[rowId].date = null; }
        if (stakeholder === 'director') { this.directorList.directors[rowId].date = null; }

      }

      return false;
    }*/


  }


  submitStakeholers(action = '') {

    // tslint:disable-next-line:prefer-const
    let copySecList = Object.assign({}, this.secList);
    if (this.secFirmList.secs.length) {
      // tslint:disable-next-line:prefer-const
      for (let i in this.secFirmList.secs) {

        // tslint:disable-next-line:prefer-const
        let formRecord: ISecretory = {
          id: this.secFirmList.secs[i].id,
          type: 'local',
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
          firm_email: this.secFirmList.secs[i].firm_email,
          firm_phone: this.secFirmList.secs[i].firm_phone,
          firm_mobile: this.secFirmList.secs[i].firm_mobile,
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

   //  return false;
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
      directors: this.directorList,
      secretories: copySecList,
      shareholders: copyShList,
      action: action
    };
    this.spinner.show();

    this.iNcoreService.incorporationDataStep2Submit(data)
      .subscribe(
        req => {

          this.spinner.hide();

          if (req['status'] === false) {

            this.changeProgressStatuses(1);
            this.step2SubmitMessage = req['message'];
            this.step2SubmitStatus = false;

            return false;

          }

          this.loadData();

        //  this.docList = req['data']['docList'];
        //  this.uploadList = req['data']['uploadList'];
         // this.uploadedList = req['data']['uploadedList'];

          if (this.validateUploadeStatus(req['data']['uploadList'], req['data']['uploadedList'])) {
            this.validateUploadeStatusFlag = true;
          } else {
            this.validateUploadeStatusFlag = false;
          }

          if (action !== 'remove') { // in case of removing stakeholder, keep the position on same page.
            //  this.loadData();
            this.changeProgressStatuses(2);
            return false;
          }

          this.directorNicLoaded = false;
          this.secNicLoaded = false;
          this.shNicLoaded = false;

          this.changeProgressStatuses(1);
          this.step2SubmitMessage = req['message'];
          this.step2SubmitStatus = true;

        },
        error => {
          this.spinner.hide();
          console.log(error);

          this.directorNicLoaded = false;
          this.secNicLoaded = false;
          this.shNicLoaded = false;

        }

      );


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

  removeSecBenif(rowId) {
    if (!rowId) {
      this.secBenifList.ben.splice(rowId, 1);
      this.validateSecShBenif();
      this.validateSec();
      return true;
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

  removeBenif(i: number, rowId: number = 0) {



    if (rowId < 0 ) {
      this.benifList.ben.splice(i, 1);
      this.validateShareHolder();
      this.validateShBenif();
    } else {
      this.shFirmList.shs[rowId].benifiList.ben.splice(i, 1 );
      this.validateShBenifEdit(rowId);
      this.validateShareHolderEdit( rowId , true);
    }
    return true;

  }

  addShBenificiaries() {

    if (this.sh.benifOwnerType === 'foreign') {
      this.sh_benif.type = 'foreign';
    } else {
      this.sh_benif.type = 'local';
    }
    // tslint:disable-next-line:prefer-const
    let copy1 = Object.assign({}, this.sh_benif);
    this.benifList.ben.push(copy1);
    this.benifList.ben.reverse();
    this.validateShareHolder();
    this.sh_benif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '', screen1Districts: [], screen1Cities: [] };
    this.validateShBenif();
  }

  addShBenificiariesEdit(i) {
    if (this.shFirmList.shs[i].benifOwnerType === 'foreign') {
      this.sh_benif.type = 'foreign';
    } else {
      this.sh_benif.type = 'local';
    }
    // tslint:disable-next-line:prefer-const
    let copy1 = Object.assign({}, this.sh_benif);
    this.shFirmList.shs[i].benifiList.ben.push(copy1);

    this.shFirmList.shs[i].benifiList.ben.reverse();

    this.validateShBenifEdit(i);
    this.validateShareHolderEdit(i, true );
    this.sh_benif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '',  screen1Districts: [], screen1Cities: [] };
  }

  checkNIC(memberType: number = 1, secShBen = false) {


    this.directorNicLoaded = false;
    this.secNicLoaded = false;
    this.shNicLoaded = false;
    this.loadNICstakeholders = false;

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
      return false;
    }

    if (type !== 'local') {
      this.directorNicLoaded = true;
      this.secNicLoaded = true;
      this.shNicLoaded = true;
      this.loadNICstakeholders = false;

      return true;
    }

    const data = {
      companyId: this.companyId,
      nic: checker,
      memberType: memberType

    };


    this.iNcoreService.incorporationNICcheck(data)
      .subscribe(
        req => {

          this.loadNICstakeholders = false;

          if (memberType === 1) {

            if (req['status'] && req['data']['member_count'] === 1) {

              this.director.firstname = req['data']['member_record'][0]['first_name'];
              this.director.title = 'Mr.';
              this.director.lastname = req['data']['member_record'][0]['last_name'];
              this.director.email = req['data']['member_record'][0]['email'];
              this.director.country = req['data']['member_record'][0]['passport_issued_country'];
              this.director.nic = req['data']['member_record'][0]['nic'];


              this.director.province = req['data']['address_record']['province'];
              this.director.district = req['data']['address_record']['district'];
              this.director.city = req['data']['address_record']['city'];
              this.director.localAddress1 = req['data']['address_record']['address1'];
              this.director.localAddress2 = req['data']['address_record']['address2'];
              this.director.postcode = req['data']['address_record']['postcode'];

              this.director.passport = req['data']['member_record'][0]['passport_no'];
              this.director.phone = req['data']['member_record'][0]['telephone'];
              this.director.mobile = req['data']['member_record'][0]['mobile'];
              this.director.share = req['data']['member_record'][0]['no_of_shares'];
              this.director.date = '';
              this.director.occupation = req['data']['member_record'][0]['occupation'];
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
            }

            return true;

          }

          if (memberType === 2) {

            if (req['status'] && req['data']['member_count'] === 1) {
              this.sec.title = 'Mr.';
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
                    this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', benifOwnerType : 'local', screen1Districts: [], screen1Cities: [], validateSecShBenifInEdit : false, secBenifList : { ben : [] } , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '' };
                    this.sec.nic = checker;
                    this.secNicLoaded = false;
                    this.loadNICstakeholders = false;
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
              this.sec = { secType: 'natural', id: 0, showEditPaneForSec: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', isShareholder: false, shareType: null, noOfSingleShares: null, coreGroupSelected: null, coreShareGroupName: '', coreShareValue: null, secCompanyFirmId: '', pvNumber: '', firm_name: '', firm_province: null, firm_district: null, firm_city: null, firm_localAddress1: null, firm_localAddress2: null, firm_postcode: null, screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '' };
              this.getProvincesForStakeHolder('sec');
              this.getDistrictsForStakeholder('sec', this.sec.province );
              this.getCitiesForStakeholder( 'sec', this.sec.district );
              this.sec.nic = checker;
              this.secNicLoaded = true;
              this.loadNICstakeholders = false;

            }
            return true;

          }

          if (memberType === 3) {

            if (req['status'] && req['data']['member_count'] === 1) {

              this.sh.title = 'Mr.';

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
              this.sh = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', screen1Districts: [], screen1Cities: [],  noOfShares: 0, shareholderType: 'natural', benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: ''};
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

    this.iNcoreService.incorporationNICcheck(data)
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


  /*************validation functions*****/

  step1Validation() {

    if (
      this.companyInfo.type_id &&
      this.companyAddress.address1 &&
      this.companyAddress.gn_division &&
      this.companyAddress.city &&
      this.companyAddress.district &&
      this.companyAddress.province &&
     // this.companyInfo.objective &&
      // tslint:disable-next-line:radix
    /*  parseInt( this.companyInfo.objective1 ) &&

     (
       // tslint:disable-next-line:radix
       ( parseInt( this.companyInfo.objective1 ) !== 999999 )  ?
      (
        // tslint:disable-next-line:radix
        parseInt( this.companyInfo.objective2 ) &&
        // tslint:disable-next-line:radix
        parseInt( this.companyInfo.objective3) &&
        // tslint:disable-next-line:radix
        parseInt( this.companyInfo.objective4 ) &&
        // tslint:disable-next-line:radix
        parseInt( this.companyInfo.objective5)
      )
      : ( this.companyInfo.otherObjective === undefined || this.companyInfo.otherObjective !== '' )
     )  && */

      // When company type is foreign or offshore

      ((this.compayType.key === 'COMPANY_TYPE_OVERSEAS' || this.compayType.key === 'COMPANY_TYPE_OFFSHORE') ?
        (this.companyForAddress.address1 &&
          this.companyForAddress.address2 &&
          this.companyForAddress.city &&
          this.companyForAddress.province &&
          this.companyForAddress.country) : true) &&

      this.companyInfo.email && this.validateEmail(this.companyInfo.email) &&
      this.validateObjectiveList()

    ) {
      this.enableStep1Submission = true;
    } else {
      this.enableStep1Submission = false;
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

    // console.log( this.isDirectorAlreadyExist( directorType ) );
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


  step2Validation() {
   /* if (!(
      this.directorList.directors.length &&
      ( this.secList.secs.length || this.secFirmList.secs.length ) &&
      ( this.shList.shs.length ||  this.shFirmList.shs.length )
      ) ) {
      this.enableStep2Submission = false;
    } else {

      if (  this.secFirmList.secs.length || this.shFirmList.shs.length ) {
        this.enableStep2Submission = true;
        return true;
      }

      // tslint:disable-next-line:prefer-const
      let directors = this.directorsNicList();
      // tslint:disable-next-line:prefer-const
      let secs = this.secNicList();
      // tslint:disable-next-line:prefer-const
      let shs = this.shareholderNicList();
      // check nic vice
      if (
        this.arraysEqual(directors.local, secs.local) && this.arraysEqual(directors.local, shs.local)
      ) {
        this.designationValidationRule = 'There has to be a minimum 2 natural persons to incorporate';
        this.enableStep2Submission = false;
      } else {
        this.designationValidationRule = '';
        this.enableStep2Submission = true;
      }

    }*/

    if (this.step2ValidationStatus) {

      if ( this.compayType.key === 'COMPANY_TYPE_PUBLIC' ||
        this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' ||
        this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34'
        ) {
          this.designationValidationRule = 'There has to be minimum 2 natural persons including 2 directors to incorporate';
        } else if ( this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') {

          this.designationValidationRule = 'There has to be  minimum one director and a power of attorney holder to register';

        } else {
          this.designationValidationRule = 'There has to be  minimum 2 natural persons to incorporate';
        }

        this.enableStep2Submission = false;
    } else {
      this.designationValidationRule = '';
      this.enableStep2Submission = true;
    }

  }

  checkdir() {
    // console.log(this.director);
    // console.log(this.directorList.directors);
  }
  checkSh() {
    // console.log(this.sh);
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

        (this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ?
         ( this.director.province && this.director.district && this.director.city && this.director.localAddress1 &&  this.director.postcode ) : true &&

        this.director.forProvince &&
        this.director.forCity &&
        this.director.country &&
        this.director.mobile && this.phonenumber(this.director.mobile, this.director.type) &&
        this.director.forAddress1 &&
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
        this.sh.firm_email && this.validateEmail(this.sh.firm_email) &&
        this.sh.firm_mobile && this.phonenumber(this.sh.firm_mobile, this.sh.type) &&
        ( this.isGuarantyCompany ? true : this.sh.shareType && (this.sh.coreGroupSelected || this.sh.noOfShares) ) &&
        this.benifList.ben.length
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
        ( this.isGuarantyCompany ? true : ( this.sh.shareType && (this.sh.coreGroupSelected || this.sh.noOfShares) ) )
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
        this.sh.firm_email && this.validateEmail(this.sh.firm_email) &&
        this.sh.firm_mobile && this.phonenumber(this.sh.firm_mobile, this.sh.type) &&
        ( this.isGuarantyCompany ? true : this.sh.shareType && (this.sh.coreGroupSelected || this.sh.noOfShares) ) &&
        this.benifList.ben.length
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
        shRow.firm_email && this.validateEmail(shRow.firm_email) &&
        shRow.firm_mobile && this.phonenumber(shRow.firm_mobile, shRow.type ) &&
       // shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares)
        ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares || (shRow.coreShareGroupName && shRow.noOfSharesGroup)) ) &&
        shRow.benifiList.ben.length
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
       ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares) )
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
        shRow.firm_email && this.validateEmail(shRow.firm_email) &&
        shRow.firm_mobile && this.phonenumber(shRow.firm_mobile, shRow.type ) &&
       //  shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares)
       ( this.isGuarantyCompany ? true : shRow.shareType && (shRow.coreGroupSelected || shRow.noOfShares) ) &&
       shRow.benifiList.ben.length
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

  validateSecForiegn() {
   if (  ! (this.sec.passport && this.sec.passport_issued_country &&
    this.sec.title && this.sec.firstname && this.sec.lastname &&
    this.sec.forProvince && this.sec.forCity && this.sec.forAddress1 && this.sec.country &&
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

  validateSec() {
    if (!(

      (
        (this.sec.secType === 'firm') ?

          (
            ((this.sec.secType === 'firm' && this.compayType.value === 'Public') ? this.sec.pvNumber : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_name : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_province : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_district : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_city : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_localAddress1 : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_postcode : true) &&
            ((this.sec.secType === 'firm') ? (this.sec.firm_mobile && this.phonenumber(this.sec.firm_mobile)) : true) &&
            ((this.sec.secType === 'firm') ? (this.sec.firm_email && this.validateEmail(this.sec.firm_email)) : true)
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
      &&
      ((this.sec.secType === 'firm' && this.sec.isShareholder) ? this.secBenifList.ben.length  : true )


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


  validateRegCheck($e) {

    this.validateSec();
    this.sec.isReg = $e ? true : false;
    this.validateSec();
    this.validateSecForiegn();
  }


  selectStakeHolderType(stakeholder, type) {

    this.loadNICstakeholders = false;

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
      this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', benifOwnerType : 'local', screen1Districts: [], screen1Cities: [], validateSecShBenifInEdit : false, secBenifList : { ben : [] } , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '' };
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
      this.sh = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', screen1Districts: [], screen1Cities: [], noOfShares: 0, shareholderType: 'natural', benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null } , passport_issued_country: '' , forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: ''};
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

  validateUploadeStatus(uploadeList, uploadedList) {

    if (this.processStatus === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT') {
      /******** validation for request docuemnt */
      // tslint:disable-next-line:prefer-const
      for (let i in uploadedList) {
        if (typeof uploadedList[i] === 'string') {

          if (!uploadedList[i]) {
        //    return false;
          }
        }
      }
      /********end validation for request docuemnt */

      // return true;
    }


    let otherDocLength = 0 ;

    if ( Object.keys(uploadeList['other']).length) {
       for ( let i in uploadeList['other'] ) {

         if ( uploadeList['other'][i]['is_required'] === true || uploadeList['other'][i]['is_required'] === 'true' ) {

            let docDBID =  uploadeList['other'][i]['dbid'];

            if ( undefined === uploadedList[docDBID]) {
              return false;
            }

            if (!uploadedList[docDBID]) {
              return false;
            } else {
               otherDocLength ++;
            }


         }
       }
    }

    if ( Object.keys(uploadeList['director']).length) {
      for ( let i in uploadeList['director'] ) {

        if ( uploadeList['director'][i]['is_required'] === true || uploadeList['director'][i]['is_required'] === 'true' ) {

           let docDBID =  uploadeList['director'][i]['dbid'];

           if ( undefined === uploadedList[docDBID]) {
             return false;
           }

           if (!uploadedList[docDBID][uploadeList['director'][i]['stakeholder_id']]) {
             return false;
           } else {
              otherDocLength ++;
           }


        }
      }
    }

   if ( Object.keys(uploadeList['sec']).length) {
    for ( let i in uploadeList['sec'] ) {

      if ( uploadeList['sec'][i]['is_required'] === true || uploadeList['sec'][i]['is_required'] === 'true' ) {

         let docDBID =  uploadeList['sec'][i]['dbid'];

         if ( undefined === uploadedList[docDBID]) {
          return false;
        }

         if (!uploadedList[docDBID][uploadeList['sec'][i]['stakeholder_id']]) {
           return false;
         } else {
            otherDocLength ++;
         }


      }
    }
   }

   if ( Object.keys(uploadeList['secFirm']).length) {
    for ( let i in uploadeList['secFirm'] ) {

      if ( uploadeList['secFirm'][i]['is_required'] === true || uploadeList['secFirm'][i]['is_required'] === 'true' ) {

         let docDBID =  uploadeList['secFirm'][i]['dbid'];

         if ( undefined === uploadedList[docDBID]) {
          return false;
        }

         if (!uploadedList[docDBID][uploadeList['secFirm'][i]['stakeholder_prefix_id']]) {
           return false;
         } else {
            otherDocLength ++;
         }


      }
    }
   }

   return true;

  /*  // tslint:disable-next-line:prefer-const
    let uploadCount = Object.keys(uploadeList['director']).length +
      Object.keys(uploadeList['sec']).length + Object.keys(uploadeList['secFirm']).length +
      otherDocLength;


    let uploadedCount;
    uploadedCount = 0;

    // tslint:disable-next-line:prefer-const
    for (let i in uploadedList) {
      if (typeof uploadedList[i] === 'string') {
        uploadedCount = uploadedCount + 1;
      } else {
        uploadedCount = uploadedCount + Object.keys(uploadedList[i]).length;
      }
    }

    // if (uploadCount === uploadedCount) {
    if (uploadedCount >= uploadCount) {
      return true;
    } else {
      return false;
    }*/


  }

  removeDoc(companyId, docTypeId, userId = null, multipleId = null , isFirm = 'no' ) {


    // tslint:disable-next-line:radix
    multipleId = (multipleId === null) ? - 1 :  parseInt(multipleId);

    const data = {
      companyId: companyId,
      docTypeId: docTypeId,
      userId: userId,
      multipleId : multipleId,
      isFirm: isFirm

    };
    this.spinner.show();
    this.iNcoreService.incorpFileRemove(data)
      .subscribe(
        rq => {

          const datas = {
            companyId: this.companyId,
            directors: this.directorList,
            secretories: this.secList,
            shareholders: this.shList

          };

          this.iNcoreService.incorporationDataStep2Submit(datas)
            .subscribe(
              req => {
                this.spinner.hide();

                this.docList = req['data']['docList'];
                this.uploadList = req['data']['uploadList'];
                this.uploadedList = req['data']['uploadedList'];

                if (this.validateUploadeStatus(req['data']['uploadList'], req['data']['uploadedList'])) {
                  this.validateUploadeStatusFlag = true;
                } else {
                  this.validateUploadeStatusFlag = false;
                }
                this.loadData();
                this.changeProgressStatuses(3);

              },
              error => {
                this.spinner.hide();
                this.changeProgressStatuses(3);
              }

            );

        },
        error => {
          this.spinner.hide();
          console.log(error);
        }

      );


  }

  validateIRD() {
     if (!
      (
        this.ird.commencementdate &&
         this.ird.bac &&
        this.ird.preferredlanguage &&
        this.ird.preferredmodeofcommunication &&
        ( (this.ird.isboireg) ? (this.ird.boistartdate && this.ird.boienddate) : true ) &&
        this.ird.purposeofregistration &&
        ((this.ird.purposeofregistration === '999' ) ? this.ird.otherpurposeofregistration : true ) &&
        ( (this.ird.isforiegncompany) ? (this.ird.dateofincorporationforeign && this.ird.countryoforigin ) : true ) &&
        ( (this.ird.parentcompanyexists)  ? (

          this.ird.localparentcompany && this.ird.parentcompanyreference && this.ird.parentcompanyreferenceid &&
          this.ird.parentcompanyname && this.ird.parentcompanyaddress && this.ird.countryofincorporation && this.ird.dateofincorporationparentcompany

        ) : true ) &&
        this.ird.contactpersonname

     )
     ) {
      this.valideIRD = false;
     } else {
      this.valideIRD = true;
     }
  }

  submitIRD() {

    this.submitIRDmessage = 'Successfully sent your infomation';
    this.changeProgressStatuses(5);
  }

  skipLabour() {
    this.changeProgressStatuses(6);
   // this.router.navigate(['/dashboard/home']);
  }

  submitLABOUR() {
    this.changeProgressStatuses(6);
  }

  /*********util functions  */
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


}

