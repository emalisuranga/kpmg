import * as $ from 'jquery';
import { ToastrService } from 'ngx-toastr';
import { NgxSpinnerService } from 'ngx-spinner';
import { HttpClient } from '@angular/common/http';
import { HttpHeaders } from '@angular/common/http';
import { ModalDirective } from 'angular-bootstrap-md';
import { Router, ActivatedRoute } from '@angular/router';
import { DomSanitizer } from '@angular/platform-browser';
import { IBuy, Item } from '../../../../../../http/models/payment';
import { IBuyDetails } from '../../../../../../storage/ibuy-details';
import { DataService } from '../../../../../../storage/data.service';
import { environment } from '../../../../../../../environments/environment';
import { Component, OnInit, AfterViewInit, ViewChild } from '@angular/core';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { CalculationService } from './../../../../../../http/shared/calculation.service';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { MemberChangeService } from '../../../../../../http/services/member-change.service';
import { IncorporationService } from '../../../../../../http/services/incorporation.service';
import { IcompanyType, Icountry, IcoreShareGroup } from '../../../../../../http/models/incorporation.model';
import { IDirectors, IDirector, ISecretories, ISecretory, IShareHolders, IShareHolder, IShareHolderBenif, IShareHolderBenifList, IProvince, IDistrict, ICity, IGnDivision } from '../../../../../../http/models/stakeholder.model';
import { AlertService } from 'ngx-alerts';

@Component({
  selector: 'app-director-secretary-change',
  templateUrl: './director-secretary-change.component.html',
  styleUrls: ['./director-secretary-change.component.scss']
})
export class DirectorSecretaryChangeComponent implements OnInit, AfterViewInit {
  @ViewChild('deleteDirector') public deleteDirector: ModalDirective;
  @ViewChild('deleteSecretary') public deleteSecretary: ModalDirective;
  @ViewChild('deleteSecFirm') public deleteSecFirm: ModalDirective;
  paymentGateway: string = environment.paymentGateway;
  url: APIConnection = new APIConnection();
  userId: number;
  aptdate: string;
  i: number;
  companyId: string;
  requestId: string;
  loggedinUserEmail: string;
  directorValitionMessage: string;
  ceasedReason: string;
  ceasedReasonOther: string;
  ceasedDate: string;
  blockPayment = false;
  enableRemoveMember = false;
  enableGoToPay = false;
  enableGoToDownload = false;
  form18UploadList = [];
  form19UploadList = [];
  form19UploadListFirm = [];
  form20UploadList = [];
  resignationUploadList = [];
  extra = [];
  additional = [];
  cipher_message: string;

  externalGlobComment = '';

  directorNicLoaded = false;
  directorPassportLoaded = false;
  resubmitSuccess = false;
  secNicLoaded = false;
  shNicLoaded = false;
  loadNICstakeholders = false;
  directorAlreadyExistMessage = '';
  secAlreadyExistMessage = '';
  step2SubmitMessage = '';
  step2SubmitStatus = false;
  validDirector = false;
  shValitionMessage = '';
  validSh = false;
  secValitionMessage = '';
  validSec = false;
  guarantee_sec_err_happend = false;
  validateShBenifFlag = false;
  validateSecShBenifFlag = false;
  openAddressPart = false;
  enableStep2Submission = false;
  enableStep2SubmissionEdit = true;
  enableStep2SubmissionOldDirectorEdit = true;
  enableStep2SubmissionOldSecEdit = true;
  enableStep2SubmissionOldSecFirmEdit = true;
  ff = '';
  members: any;
  regsecs: any;
  regsecfirms: any;
  signbyid: any;
  convert: any;
  penaltyvalue: any;
  penalty_charge: any;
  caseId: string;
  court_status = '';
  court_name = '';
  court_case_no = '';
  court_date = '';
  court_penalty = '';
  court_period = '';
  court_discharged = '';
  validateCourtSectionFlag = false;
  document_confirm = false;
  description: string;
  description1: string;

  formattedTodayValue = '';
  incoDate = '';

  form18Cost = 0;
  form19Cost = 0;
  form20Cost = 0;
  totalFormCost = 0;
  totalPayment = 0;
  form18CostKey: string;
  form19CostKey: string;
  companyTypeKey: string;

  countries: Array<Icountry> = [];
  provinces: Array<IProvince> = [];
  districts: Array<IDistrict> = [];
  cities: Array<ICity> = [];
  gns: Array<IGnDivision> = [];
  coreShareGroups: Array<IcoreShareGroup> = [];

  screen1Provinces: Array<IProvince> = [];
  screen1Districts: Array<IDistrict> = [];
  screen1Cities: Array<ICity> = [];
  screen1Gns: Array<IGnDivision> = [];

  stepOn = 0;
  processStatus = '';
  moduleStatus = '';
  progress = {
    stepArr: [
      { label: 'Change Stakeholders', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },
    ],
    progressPercentage: '12.5%'
  };

  oldSecList: ISecretories = { secs: [] };
  oldSecFirmList: ISecretories = { secs: [] };
  oldDirectorList: IDirectors = { directors: [] };

  shList: IShareHolders = { shs: [] };
  secList: ISecretories = { secs: [] };
  secFirmList: ISecretories = { secs: [] };
  directorList: IDirectors = { directors: [] };
  shFirmList: IShareHolders = { shs: [] };
  benifList: IShareHolderBenifList = { ben: [] };
  secBenifList: IShareHolderBenifList = { ben: [] };
  compayType: IcompanyType = { key: '', value: '', id: null, value_si: '', value_ta: '' };

  director: IDirector = {
    id: 0, secRegDate: '', showEditPaneForDirector: 0, type: 'local', title: '', firstname: '',
    lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '',
    passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '',
    screen1Provinces: [], screen1Districts: [], screen1Cities: [], passport_issued_country: '', forAddress1: '',
    forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec: true
  };

  sec: ISecretory = {
    id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '',
    lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '',
    nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '',
    phone: '', mobile: '', email: '', benifOwnerType: 'local', screen1Provinces: [], screen1Districts: [],
    screen1Cities: [], firm_city: '', firm_district: '', firm_province: '', validateSecShBenifInEdit: false,
    secBenifList: { ben: [] }, forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '',
    passport_issued_country: ''
  };

  public sh: IShareHolder = {
    id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '',
    province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '',
    nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '',
    shareType: 'single', noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural', screen1Provinces: [],
    screen1Districts: [], screen1Cities: [], firm_city: '', firm_district: '', firm_province: '',
    benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null }, passport_issued_country: '',
    forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: ''
  };

  public sh_benif: IShareHolderBenif = {
    type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '',
    district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '',
    date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: []
  };

  public sec_sh_benif: IShareHolderBenif = {
    type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '',
    district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '',
    date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: []
  };

  constructor(
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private general: GeneralService,
    private helper: HelperService,
    public pdc: DataService,
    private crToken: PaymentService,
    private snotifyService: ToastrService,
    private memberChange: MemberChangeService,
    private iNcoreService: IncorporationService,
    public calculation: CalculationService,
    private alertService: AlertService,
    private iBy: IBuyDetails
  ) {
    this.loggedinUserEmail = localStorage.getItem('currentUser');
    this.loggedinUserEmail = this.loggedinUserEmail.replace(/^"(.*)"$/, '$1');
    this.companyId = route.snapshot.paramMap.get('companyId');
    this.loadHeavyData();
  }
  ngOnInit() {
    this.formattedTodayValue = this.getFormatedToday();
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
    this.gotoPay();
    return this.progress;
  }
  showToggle(userType, userId = 0) {
    if (userType === 'director') {
      for (let i in this.directorList.directors) {
        if (this.directorList.directors[i]['id'] === userId) {
          this.directorList.directors[i]['showEditPaneForDirector'] = this.directorList.directors[i]['showEditPaneForDirector'] === userId ? null : userId;
          return true;
        }
      }
    }
    if (userType === 'olddirector') {
      for (let i in this.oldDirectorList.directors) {
        if (this.oldDirectorList.directors[i]['id'] === userId) {
          this.oldDirectorList.directors[i]['showEditPaneForDirector'] = this.oldDirectorList.directors[i]['showEditPaneForDirector'] === userId ? null : userId;
          return true;
        }
      }
    }
    if (userType === 'sec') {
      for (let i in this.secList.secs) {
        if (this.secList.secs[i]['id'] === userId) {
          this.secList.secs[i]['showEditPaneForSec'] = this.secList.secs[i]['showEditPaneForSec'] === userId ? null : userId;
          return true;
        }
      }
    }
    if (userType === 'oldsec') {
      for (let i in this.oldSecList.secs) {
        if (this.oldSecList.secs[i]['id'] === userId) {
          this.oldSecList.secs[i]['showEditPaneForSec'] = this.oldSecList.secs[i]['showEditPaneForSec'] === userId ? null : userId;
          return true;
        }
      }
    }
    if (userType === 'secFirm') {
      for (let i in this.secFirmList.secs) {
        if (this.secFirmList.secs[i]['id'] === userId) {
          this.secFirmList.secs[i]['showEditPaneForSec'] = this.secFirmList.secs[i]['showEditPaneForSec'] === userId ? null : userId;
          return true;
        }
      }
    }
    if (userType === 'oldsecFirm') {
      for (let i in this.oldSecFirmList.secs) {
        if (this.oldSecFirmList.secs[i]['id'] === userId) {
          this.oldSecFirmList.secs[i]['showEditPaneForSec'] = this.oldSecFirmList.secs[i]['showEditPaneForSec'] === userId ? null : userId;
          return true;
        }
      }
    }
  }
  ngAfterViewInit() {
    $(document).on('click', '.record-handler-remove', function () {
      //  let self = $(this);
      //  self.parent().parent().remove();
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
      let self = $(this);
      $('.stakeholder-type-tab-wrapper.stakeholder-dir .tab').removeClass('active');
      $(this).addClass('active');
    });
    $('.stakeholder-type-tab-wrapper.stakeholder-sec .tab').on('click', function () {
      let self = $(this);
      $('.stakeholder-type-tab-wrapper.stakeholder-sec .tab').removeClass('active');
      $(this).addClass('active');
    });
    $('.stakeholder-type-tab-wrapper.stakeholder-sh .tab').on('click', function () {
      let self = $(this);
      $('.stakeholder-type-tab-wrapper.stakeholder-sh .tab').removeClass('active');
      $(this).addClass('active');
    });


    $(document).on('click', '.reset-upload-val-action-elem', function(){
      $('input[type="file"]').val('');
    });
  }

  courtdataSubmit() {


    const data = {
      type: this.moduleStatus === 'COMPANY_CHANGE_RESUBMISSION' ? 'resubmit' : 'submit',
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

    this.memberChange.form20CourtDataSubmit(data)
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

  loadMemberData(companyId, email) {
    this.spinner.show();
    this.memberChange.memberDataLoad(companyId, email)
      .subscribe(
        req => {
          if ( req['data']['companytypeValid'] === false ) {

            this.spinner.hide();
            alert('Invalid company type');
            this.router.navigate(['/dashboard/home']);
            return false;
          }
          if ( req['data']['createrValid'] === false ) {

            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }
          if (req['status']) {
            this.moduleStatus = req['moduleStatus'];
            if (this.moduleStatus === 'COMPANY_CHANGE_RESUBMISSION' ) {
              this. document_confirm = true;
              if (req['data']['signedbytype'] === 'COMPANY_MEMBERS') {
                this.convert = req['data']['signedby'];
                this.signbyid = this.convert.toString() + '-' + 0;

              }
              else if (req['data']['signedbytype'] === 'COMPANY_MEMBER_FIRMS') {
                this.convert = req['data']['signedby'];
                this.signbyid = this.convert.toString() + '-' + 1;

              }
            }

            if ( !( this.moduleStatus === 'COMPANY_CHANGE_PROCESSING' ||  this.moduleStatus === 'COMPANY_CHANGE_RESUBMISSION' ) ) {

              this.spinner.hide();
              this.router.navigate(['/dashboard/home']);
              return false;
            }

            this.requestId = req['changeRequestID'];
            this.incoDate = req['incoDate'];
            this.oldDirectorList.directors = req['oldDirectorList'];
            this.oldSecList.secs = req['oldSecretaryList'];
            this.oldSecFirmList.secs = req['oldSecretaryFirmList'];
            this.directorList.directors = req['data']['directorList'];
            this.secList.secs = req['data']['secretaryList'];
            this.secFirmList.secs = req['data']['secretaryFirmList'];
            this.companyTypeKey = req['data']['companyTypeKey'];
            this.coreShareGroups = req['data']['coreShareGroups'];
            this.countries = req['countryList'];
            this.externalGlobComment = req['external_global_comment'];
            this.regsecs = req['data']['regsecs'];
            this.regsecfirms = req['data']['regsecfirms'];
            this.members = req['data']['members'];
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
            for (let i in this.oldDirectorList.directors) {
              this.getProvincesForStakeHolderEdit('olddirector', i);
              this.getDistrictsForStakeholderEdit('olddirector', i, this.oldDirectorList.directors[i].province, true);
              this.getCitiesForStakeholderEdit('olddirector', i, this.oldDirectorList.directors[i].district, true);
            }
            for (let i in this.oldSecList.secs) {
              this.getProvincesForStakeHolderEdit('oldsec', i);
              this.getDistrictsForStakeholderEdit('oldsec', i, this.oldSecList.secs[i].province, true);
              this.getCitiesForStakeholderEdit('oldsec', i, this.oldSecList.secs[i].district, true);
            }
            for (let i in this.oldSecFirmList.secs) {
              this.getProvincesForStakeHolderEdit('oldsecFirm', i);
              this.getDistrictsForStakeholderEdit('oldsecFirm', i, this.oldSecFirmList.secs[i].province, true);
              this.getCitiesForStakeholderEdit('oldsecFirm', i, this.oldSecFirmList.secs[i].district, true);
            }
            for (let i in this.secFirmList.secs) {
              this.getProvincesForStakeHolderEdit('secFirm', i);
              this.getDistrictsForStakeholderEdit('secFirm', i, this.secFirmList.secs[i].firm_province, true);
              this.getCitiesForStakeholderEdit('secFirm', i, this.secFirmList.secs[i].firm_district, true);
              this.secFirmList.secs[i].benifOwnerType = 'local';
            }
            for (let i in this.secList.secs) {
              this.getProvincesForStakeHolderEdit('sec', i);
              this.getDistrictsForStakeholderEdit('sec', i, this.secList.secs[i].province, true);
              this.getCitiesForStakeholderEdit('sec', i, this.secList.secs[i].district, true);
            }
            for (let i in this.directorList.directors) {
              this.getProvincesForStakeHolderEdit('director', i);
              this.getDistrictsForStakeholderEdit('director', i, this.directorList.directors[i].province, true);
              this.getCitiesForStakeholderEdit('director', i, this.directorList.directors[i].district, true);
            }
            this.form18Cost = req['data']['form18Cost'];
            this.form19Cost = req['data']['form19Cost'];
            this.form20Cost = req['data']['form20Cost'];
            this.form18CostKey = req['data']['form18CostKey'];
            this.form19CostKey = req['data']['form19CostKey'];
            this.calculatePayment();
            this.goToNext();
            this.validateCourtSection();
          }
          this.loadUploadedFile();
         // this.spinner.hide();
        }

      );
  }

  private loadHeavyData() {
    const data = {
      companyId: this.companyId,
    };
    this.spinner.show();
    this.iNcoreService.incorporationHeavyData(data)
      .subscribe(
        req => {
          this.provinces = req['data']['pdc']['provinces'];
          this.districts = req['data']['pdc']['districts'];
          this.cities = req['data']['pdc']['cities'];
          this.gns = req['data']['pdc']['gns'];
          this.loadMemberData(this.companyId, this.loggedinUserEmail);
         // this.spinner.hide();
        }
      );
  }

  saveDirectorRecord() {
    if (this.director.type === 'local') {
      this.director.country = 'Sri Lanka';
    }
    let copy = Object.assign({}, this.director);
    // this.removeDuplicatesByNIC(1);
    this.directorList.directors.push(copy);
    this.directorNicLoaded = false;
    this.secNicLoaded = false;
    this.shNicLoaded = false;
    // tslint:disable-next-line:max-line-length
    this.director = { id: 0, secRegDate: '', showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', screen1Districts: [], screen1Cities: [], passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec: true };
    // this.step2Validation();
    this.validDirector = false;
    this.submitStakeholers('remove');
  }

  saveSecRecord() {
    if (this.sec.type === 'local') {
      this.sec.country = 'Sri Lanka';
    }
    let copy1 = Object.assign({}, this.sec);
    if (this.sec.secType === 'firm') {
      copy1.secBenifList = this.secBenifList;
    }
    this.secList.secs.push(copy1);
    this.directorNicLoaded = false;
    this.secNicLoaded = false;
    this.shNicLoaded = false;
    // tslint:disable-next-line:max-line-length
    this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '', firm_date: '' };
    // this.step2Validation();
    this.validSec = false;
    this.submitStakeholers('remove');
  }

  removeDirectorRecord(i: number, userId: number = 0) {
    if (confirm('Are you sure you want to remove this director?')) {
      if (!userId) {
        return true;
      }
      const data = {
        userId: userId,
        companyId: this.companyId,
        requestId: this.requestId,
        type: 'individual'
      };
      this.spinner.show();
      this.memberChange.removeMemberData(data)
        .subscribe(
          req => {
            if (req['status']) {
              this.spinner.hide();
              this.loadMemberData(this.companyId, this.loggedinUserEmail);
              this.goToNext();
            }
          },
          error => {
            this.spinner.hide();
            console.log(error);
            alert(error);
          }
        );
    }
  }

  removeSecRecord(i: number, userId: number = 0) {
    if (confirm('Are you sure you want to remove this secretory?')) {
      if (!userId) {
        return true;
      }
      const data = {
        userId: userId,
        companyId: this.companyId,
        requestId: this.requestId,
        type: 'individual'
      };
      this.spinner.show();
      this.memberChange.removeMemberData(data)
        .subscribe(
          req => {
            if (req['status']) {
              this.spinner.hide();
              this.loadMemberData(this.companyId, this.loggedinUserEmail);
              this.goToNext();
            }
          },
          error => {
            this.spinner.hide();
            console.log(error);
            alert(error);
          }
        );
    }
  }

  removeSecFirmRecord(i: number, userId: number = 0) {
    if (confirm('Are you sure you want to remove this secretory/legal person ?')) {
      const data = {
        userId: userId,
        companyId: this.companyId,
        requestId: this.requestId,
        type: 'firm'
      };
      this.spinner.show();
      this.memberChange.removeMemberData(data)
        .subscribe(
          req => {
            if (req['status']) {
              this.spinner.hide();
              this.loadMemberData(this.companyId, this.loggedinUserEmail);
              this.goToNext();
            }
          },
          error => {
            this.spinner.hide();
            console.log(error);
            alert(error);
          }
        );
    }
  }

  removeOldDirectorRecord(i: number, userId: number = 0, email: string, date: string) {
    if (email === this.loggedinUserEmail) {
      alert('You are not allowed to remove your own director status!');
      return false;
    }
    this.userId = userId;
    this.aptdate = date;
    this.i = i;
    this.__validateDeleteMember();
    this.deleteDirector.show();
  }

  clickRemoveOldDirector() {
    this.spinner.show();
    const data = {
      userId: this.userId,
      companyId: this.requestId,
      type: 'oldIndividual',
      reason: this.ceasedReason,
      reasonOther: this.ceasedReasonOther,
      date: this.ceasedDate
    };
    this.memberChange.removeOldMemberData(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.loadMemberData(this.companyId, this.loggedinUserEmail);
            this.deleteDirector.hide();
            this.ceasedReason = '';
            this.ceasedReasonOther = '';
            this.ceasedDate = '';
            this.goToNext();
           // this.spinner.hide();
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
          alert(error);
        }
      );
  }

  removeOldSecretaryRecord(i: number, userId: number = 0, email: string, date: string) {
    if (email === this.loggedinUserEmail) {
      alert('You are not allowed to remove your own secretary status!');
      return false;
    }
    this.userId = userId;
    this.aptdate = date;
    this.i = i;
    this.__validateDeleteMember();
    this.deleteSecretary.show();
  }
  clickRemoveOldSecretary() {
    this.spinner.show();
    const data = {
      userId: this.userId,
      companyId: this.requestId,
      type: 'oldIndividual',
      reason: this.ceasedReason,
      reasonOther: this.ceasedReasonOther,
      date: this.ceasedDate
    };
    this.memberChange.removeOldMemberData(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.loadMemberData(this.companyId, this.loggedinUserEmail);
            this.deleteSecretary.hide();
            this.ceasedReason = '';
            this.ceasedReasonOther = '';
            this.ceasedDate = '';
            this.goToNext();
          //  this.spinner.hide();
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
          alert(error);
        }
      );
  }

  removeOldSecFirmRecord(i: number, userId: number = 0, date: string) {
    this.userId = userId;
    this.aptdate = date;
    this.i = i;
    this.__validateDeleteMember();
    this.deleteSecFirm.show();
  }
  clickRemoveOldSecFirm() {
    this.spinner.show();
    const data = {
      userId: this.userId,
      companyId: this.requestId,
      type: 'oldFirm',
      reason: this.ceasedReason,
      reasonOther: this.ceasedReasonOther,
      date: this.ceasedDate
    };
    this.memberChange.removeOldMemberData(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.loadMemberData(this.companyId, this.loggedinUserEmail);
            this.deleteSecFirm.hide();
            this.ceasedReason = '';
            this.ceasedReasonOther = '';
            this.ceasedDate = '';
            this.goToNext();
          //  this.spinner.hide();
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
          alert(error);
        }
      );
  }

  clickDownload(memberId, type) {
    this.partnerGeneratePDF(memberId, type);
  }
  partnerGeneratePDF(memberId, type) {
    this.spinner.show();
    this.memberChange.memberPDF(memberId, type, this.requestId)
      .subscribe(
        response => {
          this.spinner.hide();
          this.helper.download(response);
        },
        error => {
          console.log(error);
          this.spinner.hide();
        }
      );
  }

  // for upload update form 20 pdf files...
  fileUploadUpdate(event, id, description, docType) {

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
      formData.append('docId', id);
      formData.append('comId', this.companyId);
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');
      this.spinner.show();
      let uploadurl = this.url.getCompanyMemberFileUpdateUploadUrl();



      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {


            this.loadMemberData(this.companyId, this.loggedinUserEmail);
            this.gotoPay();
            this.spinner.hide();


          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }

  }

  // for uplaod members pdf files...
  fileUpload(event, docType, memberID, firmID, description) {
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      let file: File = fileList[0];
      let fileSize = fileList[0].size;
      let filetype = fileList[0].type;
      if (fileSize > 1024 * 1024 * 4) {
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
      formData.append('companyId', this.companyId);
      formData.append('filename', file.name);
      formData.append('memberID', memberID);
      formData.append('firmID', firmID);
      formData.append('description', description);
      formData.append('requestId', this.requestId);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');
      let uploadurl = this.url.getCompanyMemberFileUploadUrl();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            // const datas = {
            //   id: data['docid'],
            //   name: data['name'],
            //   token: data['token'],
            //   pdfname: data['pdfname'],
            // };
            // if (docType === 'form18Upload') {
            //   this.form18UploadList[memberID] = datas;
            // } else if (docType === 'form19Upload') {
            //   this.form19UploadList[memberID] = datas;
            // } else if (docType === 'form19UploadFirm') {
            //   this.form19UploadListFirm[firmID] = datas;
            // } else if (docType === 'form20Upload') {
            //   this.form20UploadList.push(datas);
            // } else if (docType === 'resignationUpload') {
            //   this.resignationUploadList.push(datas);
            // }
           // this.spinner.hide();
            this.loadMemberData(this.companyId, this.loggedinUserEmail);
            this.description = '';
            this.description1 = '';
            this.gotoPay();
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }
  }

  // for delete the uploaded pdf from the database...
  fileDelete(docId, docType, index) {
    const data = {
      documentId: docId,
      type: docType,
    };
    this.spinner.show();
    this.memberChange.memberDeleteUploadedPdf(data)
      .subscribe(
        response => {
          if (response['status']) {
            this.loadMemberData(this.companyId, this.loggedinUserEmail);
           // this.spinner.hide();
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
        }
      );
  }

  // for view the uploaded pdf...
  ngOnDownload(token: string): void {
    this.spinner.show();
    this.general.getDocumenttoServer(token, 'CAT_COMPANY_DOCUMENT')
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

  // for load uplaoded secretary all pdf files...
  loadUploadedFile() {
    const data = {
      companyId: this.companyId,
      requestId: this.requestId,
      type: 'memberChange',
    };
   // this.spinner.show();
    this.memberChange.memberFiles(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['file']) {
              this.form18UploadList = [];
              this.form19UploadList = [];
              this.form20UploadList = [];
              this.form19UploadListFirm = [];
              this.resignationUploadList = [];
              this.extra = [];
              this.additional = [];
              for (let i in req['data']['file']) {

                 let resubmissionStatusKey = req['data']['resubmission_status'].toString();
                 let requestStatusKey = req['data']['request_status'].toString();
                 let commentType = req['data']['external_comment_type'].toString();

                 let document_comment_type = ( req['data']['file'][i]['document_comment_type']) ? req['data']['file'][i]['document_comment_type'].toString() : '';
                 let document_status =  (req['data']['file'][i]['document_status']) ? req['data']['file'][i]['document_status'].toString() : '';

                 let docComment = '';
                 if (document_comment_type && document_status ){
                  docComment = ((resubmissionStatusKey === document_status || requestStatusKey === document_status) && commentType === document_comment_type ) ? req['data']['file'][i]['document_comment'] : '';
                 } else {
                  docComment = '';
                 }

                const data1 = {
                  id: req['data']['file'][i]['id'],
                  key: req['data']['file'][i]['dockey'],
                  token: req['data']['file'][i]['file_token'],
                  pdfname: req['data']['file'][i]['name'],
                  file_description: req['data']['file'][i]['file_description'],
                  memberid: req['data']['file'][i]['company_member_id'],
                  value: req['data']['file'][i]['value'],
                  setkey: req['data']['file'][i]['setkey'],
                  firmid: req['data']['file'][i]['company_firm_id'],
                  document_comment : docComment,
                  document_comment_type : req['data']['file'][i]['document_comment_type'],
                  document_status: req['data']['file'][i]['document_status']
                };
                if (req['data']['file'][i]['dockey'] === 'DIRSEC_CHANGE_FORM18') {
                  this.form18UploadList[req['data']['file'][i]['company_member_id']] = data1;
                } else if ((req['data']['file'][i]['dockey'] === 'DIRSEC_CHANGE_FORM19') && (req['data']['file'][i]['company_member_id'])) {
                  this.form19UploadList[req['data']['file'][i]['company_member_id']] = data1;
                } else if ((req['data']['file'][i]['dockey'] === 'DIRSEC_CHANGE_FORM19') && (req['data']['file'][i]['company_firm_id'])) {
                  this.form19UploadListFirm[req['data']['file'][i]['company_firm_id']] = data1;
                } else if (req['data']['file'][i]['dockey'] === 'DIRSEC_CHANGE_FORM20') {
                  this.form20UploadList.push(data1);
                } else if (req['data']['file'][i]['dockey'] === 'DIRSEC_CHANGE_RESIGNATION_LETTER') {
                  this.resignationUploadList.push(data1);
                } else if (req['data']['file'][i]['dockey'] === 'EXTRA_DOCUMENT') {
                  this.extra.push(data1);
                } else {
                  this.additional.push(data1);
                }
              }
              this.gotoPay();
              this.spinner.hide();
            }
          }
        }
      );
  }

  signbyInput(){
    const data = {
      companyId: this.companyId,
      requestId: this.requestId,
      signby: this.signbyid,
    };
    this.memberChange.inputSignby(data)
    .subscribe(
      req => {
        if (req['status'] === true) {
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
          this.changeProgressStatuses(1);
        }

      },
      error => {
        console.log(error);
      }
    );
  }

  revertStakeholers(id, ty, action = '') {
    const data = {
      companyId: this.companyId,
      requestId: this.requestId,
      loginUser: this.loggedinUserEmail,
      directors: this.oldDirectorList,
      secretories: this.oldSecList,
      secfirms: this.oldSecFirmList,
      type: ty,
      id: id,
      action: action
    };
    this.spinner.show();
    this.memberChange.revertMemberData(data)
      .subscribe(
        req => {
          this.spinner.hide();
          if (req['status'] === false) {
            this.changeProgressStatuses(0);
            this.step2SubmitMessage = req['message'];
            this.step2SubmitStatus = false;

            return false;
          }
          this.loadMemberData(this.companyId, this.loggedinUserEmail);
          if (action !== 'remove') {
            this.changeProgressStatuses(1);
           // this.goToNext();
            return false;
          }
          this.directorNicLoaded = false;
          this.secNicLoaded = false;
          this.shNicLoaded = false;
          this.changeProgressStatuses(0);
          this.step2SubmitMessage = req['message'];
          this.step2SubmitStatus = true;
         // this.goToNext();
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

  changeStakeholers(id, ty, action = '') {
    let copySecList = Object.assign({}, this.oldSecList);
    if (this.secFirmList.secs.length) {
      for (let i in this.secFirmList.secs) {
        let formRecord: ISecretory = {
          id: this.secFirmList.secs[i].id,
          type: this.secFirmList.secs[i].type,
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
          firm_country: this.secFirmList.secs[i].firm_country,
          firm_email: this.secFirmList.secs[i].firm_email,
          firm_phone: this.secFirmList.secs[i].firm_phone,
          firm_mobile: this.secFirmList.secs[i].firm_mobile,
          firm_date: this.secFirmList.secs[i].firm_date,
          firm_date_change: this.secFirmList.secs[i].firm_date_change,
          secType: 'firm',
          isShareholderEdit: this.secFirmList.secs[i].isShareholderEdit,
          shareTypeEdit: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].shareTypeEdit : null,
          noOfSingleSharesEdit: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].noOfSingleSharesEdit : null,
          coreGroupSelected: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].coreGroupSelected : null,
          coreShareGroupNameEdit: this.secFirmList.secs[i].coreShareGroupNameEdit ? this.secFirmList.secs[i].coreShareGroupNameEdit : null,
          coreShareValueEdit: this.secFirmList.secs[i].isShareholderEdit ? this.secFirmList.secs[i].coreShareValueEdit : null,
          secBenifList: this.secFirmList.secs[i].secBenifList,
          forAddress1: '',
          forAddress2: '',
          forCity: '',
          forProvince: '',
          passport_issued_country: '',
        };
        copySecList.secs.push(formRecord);
      }
    }
    let copyShList = Object.assign({}, this.shList);
    if (this.shFirmList.shs.length) {
      for (let i in this.shFirmList.shs) {
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
          noOfSharesGroup: this.shFirmList.shs[i].noOfSharesGroup ? this.shFirmList.shs[i].noOfSharesGroup : null,
          showEditPaneForSh: this.shFirmList.shs[i].showEditPaneForSh,
          benifiList: this.shFirmList.shs[i].benifiList
        };
        copyShList.shs.push(formRecord);
      }
    }
    const data = {
      companyId: this.companyId,
      requestId: this.requestId,
      loginUser: this.loggedinUserEmail,
      directors: this.oldDirectorList,
      secretories: this.oldSecList,
      secfirms: this.oldSecFirmList,
      shareholders: copyShList,
      type: ty,
      id: id,
      action: action
    };
    this.spinner.show();
    this.memberChange.editMemberData(data)
      .subscribe(
        req => {
          this.spinner.hide();
          if (req['status'] === false) {
            this.changeProgressStatuses(0);
            this.step2SubmitMessage = req['message'];
            this.step2SubmitStatus = false;

            return false;
          }
          this.loadMemberData(this.companyId, this.loggedinUserEmail);
          if (action !== 'remove') {
            this.changeProgressStatuses(1);
           // this.goToNext();
            return false;
          }
          this.directorNicLoaded = false;
          this.secNicLoaded = false;
          this.shNicLoaded = false;
          this.changeProgressStatuses(0);
          this.step2SubmitMessage = req['message'];
          this.step2SubmitStatus = true;
         // this.goToNext();
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

  submitStakeholers(action = '') {
    let copySecList = Object.assign({}, this.secList);
    if (this.secFirmList.secs.length) {
      for (let i in this.secFirmList.secs) {
        let formRecord: ISecretory = {
          id: this.secFirmList.secs[i].id,
          type: this.secFirmList.secs[i].type,
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
          firm_country: this.secFirmList.secs[i].firm_country,
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
          secBenifList: this.secFirmList.secs[i].secBenifList,
          forAddress1: '',
          forAddress2: '',
          forCity: '',
          forProvince: '',
          passport_issued_country: '',
        };
        copySecList.secs.push(formRecord);
      }
    }
    let copyShList = Object.assign({}, this.shList);
    if (this.shFirmList.shs.length) {
      for (let i in this.shFirmList.shs) {
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
          noOfSharesGroup: this.shFirmList.shs[i].noOfSharesGroup ? this.shFirmList.shs[i].noOfSharesGroup : null,
          showEditPaneForSh: this.shFirmList.shs[i].showEditPaneForSh,
          benifiList: this.shFirmList.shs[i].benifiList
        };
        copyShList.shs.push(formRecord);
      }
    }
    const data = {
      companyId: this.companyId,
      requestId: this.requestId,
      loginUser: this.loggedinUserEmail,
      directors: this.directorList,
      secretories: copySecList,
      shareholders: copyShList,
      action: action
    };
    this.spinner.show();
    this.memberChange.saveMemberData(data)
      .subscribe(
        req => {
          this.spinner.hide();
          if (req['status'] === false) {
            this.changeProgressStatuses(0);
            this.step2SubmitMessage = req['message'];
            this.step2SubmitStatus = false;

            return false;
          }
          this.loadMemberData(this.companyId, this.loggedinUserEmail);
          if (action !== 'remove') {
            this.changeProgressStatuses(1);
           // this.goToNext();
            return false;
          }
          this.directorNicLoaded = false;
          this.secNicLoaded = false;
          this.shNicLoaded = false;
          this.changeProgressStatuses(0);
          this.step2SubmitMessage = req['message'];
          this.step2SubmitStatus = true;
         // this.goToNext();
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

  isDirectorAlreadyExistAction(directorType = 'local') {
    let message = (directorType === 'foreign') ?
      'This Director Already Exists. Please Try a Different passport number' :
      'This Director Already Exists. Please try a Different NIC';
    if (this.isDirectorAlreadyExist(directorType)) {
      if (directorType === 'local') {
        this.directorNicLoaded = false;
      }
      this.directorAlreadyExistMessage = message;
    } else {
      this.directorAlreadyExistMessage = '';
      if (directorType === 'local') {
        this.checkNIC(1);
      }
    }
  }

  // to load data using nic number...
  checkNIC(memberType: number = 1, secShBen = false) {

    this.directorNicLoaded = false;
    this.secNicLoaded = false;
    this.shNicLoaded = false;
    this.loadNICstakeholders = false;
    this.openAddressPart = false;
    this.openAddressPart = false;
    let checker = (memberType === 1) ? this.director.nic : (memberType === 2) ? this.sec.nic : this.sh.nic;
    if (secShBen) {
      checker = this.sec.nic;
    }
    let type = (memberType === 1) ? this.director.type : (memberType === 2) ? this.sec.type : this.sh.type;
    if (!checker) {
      this.directorNicLoaded = false;
      this.secNicLoaded = false;
      this.shNicLoaded = false;
      this.loadNICstakeholders = false;
      this.openAddressPart = false;
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
    this.iNcoreService.incorporationNICcheck(data)
      .subscribe(
        req => {

          this.loadNICstakeholders = false;
          this.openAddressPart = req['data']['openLocalAddress'];
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
              if (this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' ||
                this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34' ||
                this.compayType.key === 'COMPANY_TYPE_PUBLIC') {
                if (req['data']['sec_reg_no']) {
                  this.director.secRegDate = req['data']['sec_reg_no'];
                  this.guarantee_sec_err_happend = false;
                } else {
                  this.director.secRegDate = '';
                  this.guarantee_sec_err_happend = true;
                }
              }
              this.getProvincesForStakeHolder('director');
              this.getDistrictsForStakeholder('director', this.director.province, true);
              this.getCitiesForStakeholder('director', this.director.district, true);
              this.loadNICstakeholders = true;
              this.validateDirector();
            } else {
              // tslint:disable-next-line:max-line-length
              this.director = { id: 0, showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: null, noOfSingleShares: null, coreGroupSelected: null, coreShareGroupName: '', coreShareValue: null, screen1Districts: [], screen1Cities: [], passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec: true }; if (this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' ||
                this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34' ||
                this.compayType.key === 'COMPANY_TYPE_PUBLIC') {
                if (req['data']['sec_reg_no']) {
                  this.director.secRegDate = req['data']['sec_reg_no'];
                  this.guarantee_sec_err_happend = false;
                } else {
                  this.guarantee_sec_err_happend = true;
                }
              }
              this.getProvincesForStakeHolder('director');
              this.getDistrictsForStakeholder('director', this.director.province);
              this.getCitiesForStakeholder('director', this.director.district);
              this.director.nic = checker;
              this.directorNicLoaded = true;
              this.loadNICstakeholders = false;
              this.openAddressPart = false;
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
              this.sec.regDate = (req['data']['member_record'][0]['secretary_registration_no']) ? req['data']['member_record'][0]['secretary_registration_no'] : this.sec.regDate = req['data']['sec_reg_no'];
              if (this.sec.regDate) {
                this.sec.isReg = true;
              }
              if (this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' ||
                this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34' ||
                this.compayType.key === 'COMPANY_TYPE_PUBLIC') {
                this.sec.regDate = req['data']['sec_reg_no'];
                if (this.sec.regDate) {
                  this.sec.isReg = true;
                  this.guarantee_sec_err_happend = false;
                } else {
                  this.guarantee_sec_err_happend = true;
                  // tslint:disable-next-line:max-line-length
                  this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', benifOwnerType: 'local', screen1Districts: [], screen1Cities: [], validateSecShBenifInEdit: false, secBenifList: { ben: [] }, forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '', firm_date: '' };
                  this.sec.nic = checker;
                  this.secNicLoaded = false;
                  this.loadNICstakeholders = false;
                  this.openAddressPart = false;
                  return false;
                }
              }
              this.sec.secType = (req['data']['member_record'][0]['company_member_firm_id']) ? 'firm' : 'natural';
              this.getProvincesForStakeHolder('sec');
              this.getDistrictsForStakeholder('sec', this.sec.province, true);
              this.getCitiesForStakeholder('sec', this.sec.district, true);
              this.validateSec();
              this.secNicLoaded = true;
              this.loadNICstakeholders = true;
            } else { // reset
              // tslint:disable-next-line:max-line-length
              this.sec = { secType: 'natural', id: 0, showEditPaneForSec: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', isShareholder: false, shareType: null, noOfSingleShares: null, coreGroupSelected: null, coreShareGroupName: '', coreShareValue: null, secCompanyFirmId: '', pvNumber: '', firm_name: '', firm_province: null, firm_district: null, firm_city: null, firm_localAddress1: null, firm_localAddress2: null, firm_postcode: null, screen1Districts: [], screen1Cities: [], forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '', firm_date: '' };
              this.getProvincesForStakeHolder('sec');
              this.getDistrictsForStakeholder('sec', this.sec.province);
              this.getCitiesForStakeholder('sec', this.sec.district);
              this.sec.nic = checker;
              if (this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' ||
                this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34' ||
                this.compayType.key === 'COMPANY_TYPE_PUBLIC') {
                this.guarantee_sec_err_happend = true;
                this.secNicLoaded = false;
              } else {
                this.secNicLoaded = true;
                this.guarantee_sec_err_happend = false;
              }
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
              this.getDistrictsForStakeholder('sh', this.sh.province, true);
              this.getCitiesForStakeholder('sh', this.sh.district, true);
              // this.validateShareHolder();
              if (secShBen) {
                this.secNicLoaded = true;
              } else {
                this.shNicLoaded = true;
              }
              this.loadNICstakeholders = true;
            } else { // reset
              // tslint:disable-next-line:max-line-length
              this.sh = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', screen1Districts: [], screen1Cities: [], noOfShares: 0, shareholderType: 'natural', benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null }, passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: '' };
              this.getProvincesForStakeHolder('sh');
              this.getDistrictsForStakeholder('sh', this.sh.province);
              this.getCitiesForStakeholder('sh', this.sh.district);
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
        },
        error => {
          console.log(error);
        }
      );
  }

  selectStakeHolderType(stakeholder, type) {
    this.loadNICstakeholders = false;
    if (stakeholder === 'director') {
      this.director = { id: 0, showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', screen1Districts: [], screen1Cities: [], passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec: true };
      this.director.type = type;
      // this.directorAlreadyExistMessage = '';
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
      this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', benifOwnerType: 'local', screen1Districts: [], screen1Cities: [], validateSecShBenifInEdit: false, secBenifList: { ben: [] }, forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '' };
      this.sec.type = type;
      // this.secAlreadyExistMessage = '';
      if (this.sec.type !== 'local') {
        this.secNicLoaded = true;
        this.getProvincesForStakeHolder('sec');
      } else {
        this.getProvincesForStakeHolder('sec');
        this.secNicLoaded = false;
      }
      this.validateSec();
    }
  }

  getProvincesForStakeHolder(type) {
    let provinces = Object.assign({}, this.provinces);
    let filterProvince: Array<IProvince> = [];
    for (let i in provinces) {
      filterProvince.push(provinces[i]);
    }
    if (type === 'director') {
      this.director.screen1Provinces = filterProvince;
      this.validateDirector();
    }
    if (type === 'sec') {
      this.sec.screen1Provinces = filterProvince;
      this.validateSec();
    }
  }

  getDistrictsForStakeholder(type, provinceName, load = false) {
    let districts = Object.assign({}, this.districts);
    let filterDistricts: Array<IDistrict> = [];
    for (let i in districts) {
      if (districts[i].provinceName === provinceName) {
        filterDistricts.push(districts[i]);
      }
    }
    if (type === 'director') {
      this.director.screen1Districts = filterDistricts;
      if (load === false) {
        this.director.city = '';
        this.director.district = '';
      }
      this.validateDirector();
    }
    if (type === 'sec') {
      this.sec.screen1Districts = filterDistricts;
      if (load === false) {
        this.sec.city = '';
        this.sec.district = '';
        this.sec.firm_city = '';
        this.sec.firm_district = '';
      }
      this.validateSec();
    }
  }

  getCitiesForStakeholder(type, districtName, load = false) {
    let cities = Object.assign({}, this.cities);
    let filterCities: Array<ICity> = [];
    for (let i in cities) {
      if (cities[i].districtName === districtName) {
        filterCities.push(cities[i]);
      }
    }
    if (type === 'director') {
      this.director.screen1Cities = filterCities;
      if (load === false) {
        this.director.city = '';
      }
      this.validateDirector();
    }
    if (type === 'sec') {
      this.sec.screen1Cities = filterCities;
      if (load === false) {
        this.sec.city = '';
        this.sec.firm_city = '';
      }
      this.validateSec();
    }
  }

  isSecAlreadyExistAction() {
    let message = 'This Secretory Already Exists. Please Try a Different NIC';
    if (this.isSecAlreadyExist()) {
      this.secNicLoaded = false;
      this.secAlreadyExistMessage = message;
    } else {
      this.secAlreadyExistMessage = '';
      this.checkNIC(2);
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

  isShAlreadyExistForSec(shType = 'local') {
    const shList = this.shareholderNicList();
    const shLocalList = shList.local;
    return (shLocalList.indexOf(this.sec.nic.toLowerCase()) > -1);
  }

  resetSecRecord() {
    let conf = confirm('Are you sure you want to reset ?');
    if (!conf) {
      return true;
    }
    // tslint:disable-next-line:max-line-length
    this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', isShareholder: false, shareType: null, noOfSingleShares: null, coreGroupSelected: null, coreShareGroupName: '', coreShareValue: null, secCompanyFirmId: '', pvNumber: '', firm_name: '', firm_province: null, firm_district: null, firm_city: null, firm_localAddress1: null, firm_localAddress2: null, firm_postcode: null, benifOwnerType: 'local', screen1Districts: [], screen1Cities: [], validateSecShBenifInEdit: false, secBenifList: { ben: [] }, forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', passport_issued_country: '', firm_date: '' };
    this.secBenifList = { ben: [] };
    this.loadNICstakeholders = false;
    this.openAddressPart = false;
    this.guarantee_sec_err_happend = false;
    this.secNicLoaded = false;
  }

  validateRegCheck($e) {
    this.validateSec();
    this.sec.isReg = $e ? true : false;
    if (this.sec.isReg === false){
      this.sec.regDate = null;
    }
    this.validateSec();
    // this.validateSecForiegn();
  }

  validateSecForiegn() {
    if (!(this.sec.passport && this.sec.passport_issued_country &&
      this.sec.title && this.sec.firstname && this.sec.lastname &&
      this.sec.forProvince && this.sec.forCity && this.sec.forAddress1 && this.sec.country &&
      this.sec.date &&
      this.sec.mobile && this.phonenumber(this.sec.mobile, 'foreign') &&
      this.sec.email && this.validateEmail(this.sec.email)
    )) {
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

  validateSecShBenif() {
    if (this.sec.benifOwnerType === 'local') {
      if (!
        (this.sec_sh_benif.nic && this.validateNIC(this.sec_sh_benif.nic) &&
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

  changeDefaultStatus() {
    // tslint:disable-next-line:max-line-length
    this.director = { id: 0, secRegDate: '', showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [], passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', can_director_as_sec: true };
    // tslint:disable-next-line:max-line-length
    this.sec = { id: 0, showEditPaneForSec: 0, type: 'local', secType: 'natural', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', isReg: false, regDate: '', phone: '', mobile: '', email: '', benifOwnerType: 'local', screen1Provinces: [], screen1Districts: [], screen1Cities: [], firm_city: '', firm_district: '', firm_province: '', validateSecShBenifInEdit: false, secBenifList: { ben: [] } };
    // tslint:disable-next-line:max-line-length
    // this.sh = { id: 0, showEditPaneForSh: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', firm_localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '', shareType: 'single', noOfShares: 0, noOfSharesGroup: null, shareholderType: 'natural', screen1Provinces: [], screen1Districts: [], screen1Cities: [], firm_city: '', firm_district: '', firm_province: '', benifOwnerType: 'local', shareRow: { name: '', no_of_shares: null, type: null }, passport_issued_country: '', forAddress1: '', forAddress2: '', forPostcode: '', forProvince: '', forCity: '', firm_date: '' };
    this.sh_benif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] };
    this.sec_sh_benif = { type: 'local', id: 0, title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', date: '', occupation: '', phone: '', mobile: '', email: '', screen1Provinces: [], screen1Districts: [], screen1Cities: [] };

    this.director.type = 'local';
    this.sec.type = 'local';
    this.sh.type = 'local';
    this.loadNICstakeholders = false;
    this.directorNicLoaded = false;
    this.openAddressPart = false;
    this.secNicLoaded = false;
    this.shNicLoaded = false;

    this.getProvincesForStakeHolder('director');
    this.getProvincesForStakeHolder('sec');
    this.getProvincesForStakeHolder('sh');

    // this.getProvincesForBen('sec_sh_benif');
    // this.getProvincesForBen('sh_benif');
    this.guarantee_sec_err_happend = false;

  }


  validateDirector() {
    if (this.director.isShareholder && this.director.shareType === 'core') {
      this.ff = 'setmax';
    } else {
      this.ff = '';
    }
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
          (((this.compayType.key === 'COMPANY_TYPE_PUBLIC' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34') && this.director.isSec) ? this.director.secRegDate : true) &&

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
          true : (this.director.province && this.director.district && this.director.city && this.director.localAddress1 && this.director.postcode)) &&
        this.director.forProvince &&
        this.director.forCity &&
        this.director.country &&
        this.director.mobile && this.phonenumber(this.director.mobile, this.director.type) &&
        this.director.forAddress1 &&
        ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ? true : this.director.forPostcode) &&
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

  validateSec() {
    if (!(
      (
        (this.sec.secType === 'firm') ?
          (
            ((this.sec.secType === 'firm' && (this.companyTypeKey === 'COMPANY_TYPE_PUBLIC' || this.companyTypeKey === 'COMPANY_TYPE_GUARANTEE_32' || this.companyTypeKey === 'COMPANY_TYPE_GUARANTEE_34')) ? (this.sec.pvNumber && this.sec.cvNumber) : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_name : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_date : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_province : true) &&
            ((this.sec.secType === 'firm' && !(this.companyTypeKey === 'COMPANY_TYPE_OFFSHORE' || this.companyTypeKey === 'COMPANY_TYPE_OVERSEAS')) ? this.sec.firm_district : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_city : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_localAddress1 : true) &&
            ((this.sec.secType === 'firm') ? this.sec.firm_postcode : true) &&
            ((this.sec.pvNumber) ? this.validateRegNo(this.sec.pvNumber, '0', 'secfirm') : true) &&
            ((this.sec.secType === 'firm') ? (this.sec.firm_mobile && this.phonenumber(this.sec.firm_mobile, this.sec.type)) : true) &&
            ((this.sec.secType === 'firm') ? (this.sec.firm_email && this.validateEmail(this.sec.firm_email)) : true) &&
            ((this.sec.secType === 'firm' && this.sec.type !== 'local' && (this.companyTypeKey === 'COMPANY_TYPE_OFFSHORE' || this.companyTypeKey === 'COMPANY_TYPE_OVERSEAS')) ? this.sec.firm_country : true)
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
            ((this.sec.nic && this.sec.regDate) ? this.validateRegNo(this.sec.nic, this.sec.regDate, 'sec') : true) &&
            ((this.companyTypeKey === 'COMPANY_TYPE_PUBLIC' || this.companyTypeKey === 'COMPANY_TYPE_GUARANTEE_32' || this.companyTypeKey === 'COMPANY_TYPE_GUARANTEE_34') ? this.sec.regDate : true))
      ) &&
      ((this.sec.isShareholder === undefined || this.sec.isShareholder === false) || this.sec.shareType === 'single' && this.sec.noOfSingleShares ||
        this.sec.shareType === 'core' && this.sec.coreGroupSelected ||
        this.sec.shareType === 'core' && (this.sec.coreShareGroupName && this.sec.coreShareValue)
      )
      &&
      ((this.sec.secType === 'firm' && this.sec.isShareholder) ? this.secBenifList.ben.length : true)
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

  isSecAlreadyExist() {
    const secList = this.secNicList();
    const secLocalList = secList.local;
    return (this.sec.nic && secLocalList.indexOf(this.sec.nic.toLowerCase()) > -1);
  }

  isSecAlreadyExistForDirector(nic) {
    const secList = this.secNicList();
    const secLocalList = secList.local;
    return (nic && secLocalList.indexOf(nic.toLowerCase()) > -1);
  }

  isShAlreadyExistForDirector(shType = 'local', nicOrPassport) {
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

  secNicList() {
    let secs = this.secList.secs;
    let secNICList = {
      'local': [],
    };
    if (!secs.length) {
      return secNICList;
    }
    for (let i in secs) {
      if (secs[i].nic) {
        secNICList.local.push(secs[i].nic.toLowerCase());
      }
    }
    return secNICList;
  }

  directorsNicList() {
    let directors = this.directorList.directors;
    let directorNICList = {
      'local': [],
      'foreign': []
    };
    if (!directors.length) {
      return directorNICList;
    }
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

  shareholderNicList() {
    let shs = this.shList.shs;
    let shNICList = {
      'local': [],
      'foreign': []
    };
    if (!shs.length) {
      return shNICList;
    }
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

  private validateEmail(email) {
    if (!email) { return false; }
    let re = /^[A-Za-z0-9]([a-zA-Z0-9]+([_.-][a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,3})$/;
    return re.test(email);
  }
  private phonenumber(inputtxt, type = 'local') {
    if (!inputtxt) { return true; }
    let phoneno = type === 'foreign' ? /^\d{10,15}$/ : /^\d{10}$/;
    return inputtxt.match(phoneno);
  }
  private validateNIC(nic) {
    if (!nic) {
      return false;
    }
    let regx = /^[0-9]{9}[x|X|v|V]|[0-9]{12}$/;
    return nic.match(regx);
  }

  private validateRegNo(nic: string, regno: string, type) {
    if ((!nic) || (!regno)) {
      return true;
    }
    if (type === 'sec'){
      for (let i in this.regsecs) {
        if (this.regsecs[i].nic === nic.toUpperCase() && this.regsecs[i].certno === regno) {
          return true;
        }
      }
      return false;

    }
    else if (type === 'secfirm'){
      for (let i in this.regsecfirms) {
        if (this.regsecfirms[i].certno === nic) {
          return true;
        }
      }
      return false;

    }

  }

  // private validateRegNo(nic, regno, type) {
  //   if ((!nic) || (!regno)) {
  //     return false;
  //   }
  //   const data = {
  //     companyId: this.companyId,
  //     requestId: this.requestId,
  //     loginUser: this.loggedinUserEmail,
  //     nic: nic,
  //     regno: regno,
  //     type: type,
  //   };
  //   this.memberChange.checkRegno(data)
  //     .subscribe(
  //       req => {
  //         if (req['status'] === false) {
  //           console.log('11');

  //           return false;
  //         }
  //         else if (req['status'] === true) {
  //           console.log('22');

  //           return true;
  //         }
  //       },
  //       error => {
  //         this.spinner.hide();
  //         console.log(error);
  //       }
  //     );
  // }

  validateOppDate(type = 'add', stakeholder = 'director', rowId = 0) { }

  __validateDeleteMember() {
    if ((this.ceasedReason === 'Other' ? this.ceasedReasonOther : this.ceasedReason) &&
      this.ceasedDate) {
      this.enableRemoveMember = true;
    } else {
      this.enableRemoveMember = false;
    }
  }

  gotoPay() {
   // console.log(this.form18UploadList);
   // console.log(this.form19UploadList);
  //  console.log(this.form19UploadListFirm);
  //  console.log(this.form20UploadList);
   // console.log(this.resignationUploadList);

    let x = 0;
    let y = 0;
    let z = 0;
    for (let item of this.form18UploadList) {
      if (item) {
        x = x + 1;
      }
      else {
        continue;
      }
    }
    for (let item of this.form19UploadList) {
      if (item) {
        y = y + 1;
      }
      else {
        continue;
      }
    }
    for (let item of this.form19UploadListFirm) {
      if (item) {
        z = z + 1;
      }
      else {
        continue;
      }
    }


    if ((typeof this.form18UploadList !== 'undefined' && this.form18UploadList != null && this.form18UploadList.length != null && (x === this.directorList.directors.length)) &&
      (((y + z) === (this.secList.secs.length + this.secFirmList.secs.length))) &&
      (typeof this.form20UploadList !== 'undefined' && this.form20UploadList != null && this.form20UploadList.length != null && this.form20UploadList.length > 0)) {
      this.enableGoToPay = true;
    } else {
      this.enableGoToPay = false;
    }
  }
  // for confirm to complete payment step...
  areYouSurePayYes() {
    this.getCipherToken();
    this.blockPayment = true;
  }
  areYouSurePayNo() {
    this.blockPayment = false;
  }
  getCipherToken() {
    const item: Array<Item> = [{
      fee_type: this.form18CostKey,
      description: 'Form 18 cost',
      quantity: this.directorList.directors.length,
    },
    {
      fee_type: this.form19CostKey,
      description: 'Form 19 cost',
      quantity: this.secList.secs.length,
    },
    {
      fee_type: 'PAYMENT_FORM20',
      description: 'Form 20 cost',
      quantity: 1,
    }];
    const buy: IBuy = {
      module_type: 'MODULE_DIR_SEC_CHANGE',
      module_id: this.requestId.toString(),
      description: 'Company Director Secretary Change',
      item: item,
      extraPay: null,
      penalty: (this.court_status === 'yes') ? '0' :  this.penaltyvalue.toString()
    };
    this.crToken.getCrToken(buy).subscribe(
      req => {
        this.cipher_message = req.token;
        this.blockPayment = true;
      },
      error => { this.snotifyService.error(error, 'error'); }
    );
  }

  validateRegCheckEdit($e, rowId) {
    let secRow = this.secList.secs[rowId];
    secRow.isReg = $e ? true : false;
    if (secRow.isReg === false){
      secRow.regDate = null;
    }
    this.validateSecEdit(rowId);
  }

  validateOldDirectorEdit(rowId) {
    // tslint:disable-next-line:prefer-const
    let directorRow = this.oldDirectorList.directors[rowId];
    if (directorRow.type === 'local') {
      if (!(directorRow.nic && this.validateNIC(directorRow.nic) &&
        // this.director.title &&
        directorRow.email && this.validateEmail(directorRow.email) &&
        directorRow.firstname &&
        directorRow.lastname &&
        directorRow.title &&
        directorRow.province &&
        directorRow.district &&
        directorRow.city &&
        directorRow.mobile && this.phonenumber(directorRow.mobile, directorRow.type) &&
        directorRow.localAddress1 &&
        directorRow.postcode &&
        directorRow.date &&
        directorRow.changedate &&
        (((this.compayType.key === 'COMPANY_TYPE_PUBLIC' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34') && directorRow.isSecEdit) ? directorRow.secRegDate : true) &&
        ((directorRow.isShareholderEdit === undefined || directorRow.isShareholderEdit === false) || directorRow.shareTypeEdit === 'single' && directorRow.noOfSingleSharesEdit ||
          directorRow.shareTypeEdit === 'core' && directorRow.coreGroupSelectedEdit ||
          directorRow.shareTypeEdit === 'core' && (directorRow.coreShareGroupNameEdit && directorRow.coreShareValueEdit)
        )
      )
      ) {
        //  this.directorValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.enableStep2Submission = false;
        this.enableStep2SubmissionOldDirectorEdit = false;
        this.oldDirectorList.directors[rowId]['validEdit'] = false;
        return false;
      } else {
        // this.directorValitionMessage = '';
        this.enableStep2Submission = true;
        this.enableStep2SubmissionOldDirectorEdit = true;
        this.oldDirectorList.directors[rowId]['validEdit'] = true;
        return true;
      }
    }
    if (directorRow.type === 'foreign') {
      if (!(directorRow.passport && directorRow.passport_issued_country &&
        // this.director.title &&
        directorRow.email && this.validateEmail(directorRow.email) &&
        directorRow.firstname &&
        directorRow.lastname &&
        directorRow.changedate &&
        directorRow.forProvince &&
        directorRow.forCity &&
        directorRow.mobile && this.phonenumber(directorRow.mobile, directorRow.type) &&
        directorRow.forAddress1 &&
        ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ?
          true : (directorRow.province && directorRow.district && directorRow.city && directorRow.localAddress1 && directorRow.postcode)) &&
        // directorRow.province && directorRow.district && directorRow.city && directorRow.localAddress1 &&  directorRow.postcode &&
        directorRow.country &&
        ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ? true : directorRow.forPostcode) &&
        directorRow.date &&
        ((directorRow.isShareholderEdit === undefined || directorRow.isShareholderEdit === false) || directorRow.shareTypeEdit === 'single' && directorRow.noOfSingleSharesEdit ||
          directorRow.shareTypeEdit === 'core' && directorRow.coreGroupSelectedEdit ||
          directorRow.shareTypeEdit === 'core' && (directorRow.coreShareGroupNameEdit && directorRow.coreShareValueEdit)
        )
      )) {
        // this.directorValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.enableStep2Submission = false;
        this.enableStep2SubmissionOldDirectorEdit = false;
        this.oldDirectorList.directors[rowId]['validEdit'] = false;
        return false;
      } else {
        // this.directorValitionMessage = '';
        this.enableStep2Submission = true;
        this.enableStep2SubmissionOldDirectorEdit = true;
        this.oldDirectorList.directors[rowId]['validEdit'] = true;
        return true;
      }
    }
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
        directorRow.title &&
        directorRow.province &&
        directorRow.district &&
        directorRow.city &&
        directorRow.mobile && this.phonenumber(directorRow.mobile, directorRow.type) &&
        directorRow.localAddress1 &&
        directorRow.postcode &&
        directorRow.date &&
        (((this.compayType.key === 'COMPANY_TYPE_PUBLIC' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34') && directorRow.isSecEdit) ? directorRow.secRegDate : true) &&
        ((directorRow.isShareholderEdit === undefined || directorRow.isShareholderEdit === false) || directorRow.shareTypeEdit === 'single' && directorRow.noOfSingleSharesEdit ||
          directorRow.shareTypeEdit === 'core' && directorRow.coreGroupSelectedEdit ||
          directorRow.shareTypeEdit === 'core' && (directorRow.coreShareGroupNameEdit && directorRow.coreShareValueEdit)
        )
      )
      ) {
        //  this.directorValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.enableStep2Submission = false;
        this.enableStep2SubmissionEdit = false;
        this.directorList.directors[rowId]['validEdit'] = false;
        return false;
      } else {
        // this.directorValitionMessage = '';
        this.enableStep2Submission = true;
        this.enableStep2SubmissionEdit = true;
        this.directorList.directors[rowId]['validEdit'] = true;
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
          true : (directorRow.province && directorRow.district && directorRow.city && directorRow.localAddress1 && directorRow.postcode)) &&
        // directorRow.province && directorRow.district && directorRow.city && directorRow.localAddress1 &&  directorRow.postcode &&
        directorRow.country &&
        ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ? true : directorRow.forPostcode) &&
        directorRow.date &&
        ((directorRow.isShareholderEdit === undefined || directorRow.isShareholderEdit === false) || directorRow.shareTypeEdit === 'single' && directorRow.noOfSingleSharesEdit ||
          directorRow.shareTypeEdit === 'core' && directorRow.coreGroupSelectedEdit ||
          directorRow.shareTypeEdit === 'core' && (directorRow.coreShareGroupNameEdit && directorRow.coreShareValueEdit)
        )
      )) {
        // this.directorValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.enableStep2Submission = false;
        this.enableStep2SubmissionEdit = false;
        this.directorList.directors[rowId]['validEdit'] = false;
        return false;
      } else {
        // this.directorValitionMessage = '';
        this.enableStep2Submission = true;
        this.enableStep2SubmissionEdit = true;
        this.directorList.directors[rowId]['validEdit'] = true;
        return true;
      }
    }
  }
  validateOldSecEdit(rowId) {
    let secRow = this.oldSecList.secs[rowId];
    if (!(
      ((secRow.secType === 'firm' && this.compayType.value === 'Public') ? secRow.pvNumber : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_name : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_province : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_district : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_city : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_localAddress1 : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_postcode : true) &&
      ((secRow.type === 'local') ? secRow.nic && this.validateNIC(secRow.nic) : (secRow.passport && secRow.passport_issued_country)) &&
      secRow.date &&
      secRow.changedate &&
      secRow.firstname &&
      secRow.lastname &&
      secRow.title &&
      (secRow.type === 'local' ? secRow.province : true) &&
      (secRow.type === 'local' ? secRow.district : true) &&
      (secRow.type === 'local' ? secRow.city : true) &&
      (secRow.type === 'local' ? secRow.postcode : true) &&
      (secRow.type === 'foreign' ? secRow.forProvince : true) &&
      (secRow.type === 'foreign' ? secRow.forCity : true) &&
      (secRow.type === 'foreign' ? ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ? true : secRow.forPostcode) : true) &&
      secRow.mobile && this.phonenumber(secRow.mobile, secRow.type) &&
      secRow.email && this.validateEmail(secRow.email) &&
      (secRow.type === 'local' ? secRow.localAddress1 : true) &&
      (secRow.type === 'foreign' ? secRow.forAddress1 : true) &&
      // ((this.compayType.key === 'COMPANY_TYPE_PUBLIC' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_32' || this.compayType.key === 'COMPANY_TYPE_GUARANTEE_34') ? secRow.regDate : true) &&
      ((secRow.isShareholderEdit === undefined || secRow.isShareholderEdit === false) || secRow.shareTypeEdit === 'single' && secRow.noOfSingleSharesEdit ||
        secRow.shareTypeEdit === 'core' && secRow.coreGroupSelectedEdit ||
        secRow.shareTypeEdit === 'core' && (secRow.coreShareGroupNameEdit && secRow.coreShareValueEdit)
      )
    )) {
      this.enableStep2Submission = false;
      this.enableStep2SubmissionOldSecEdit = false;
      this.oldSecList.secs[rowId]['validEdit'] = false;
      return false;
    } else {
      if (secRow.isReg) {
        if (!secRow.regDate) {
          this.enableStep2Submission = false;
          this.enableStep2SubmissionOldSecEdit = false;
          this.oldSecList.secs[rowId]['validEdit'] = false;
          return false;
        } else {
          this.enableStep2Submission = true;
          this.enableStep2SubmissionOldSecEdit = true;
          this.oldSecList.secs[rowId]['validEdit'] = true;
          return true;
        }
      } else {
        this.enableStep2Submission = true;
        this.enableStep2SubmissionOldSecEdit = true;
        this.oldSecList.secs[rowId]['validEdit'] = true;
        return true;
      }
    }
  }

  validateSecEdit(rowId) {
    let secRow = this.secList.secs[rowId];
    if (!(
      ((secRow.secType === 'firm' && (this.companyTypeKey === 'COMPANY_TYPE_PUBLIC' || this.companyTypeKey === 'COMPANY_TYPE_GUARANTEE_32' || this.companyTypeKey === 'COMPANY_TYPE_GUARANTEE_34')) ? secRow.pvNumber : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_name : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_province : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_district : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_city : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_localAddress1 : true) &&
      ((secRow.pvNumber) ? this.validateRegNo(secRow.pvNumber, '0', 'secfirm') : true) &&
      ((secRow.secType === 'firm') ? secRow.firm_postcode : true) &&
      ((secRow.type === 'local') ? secRow.nic && this.validateNIC(secRow.nic) : (secRow.passport && secRow.passport_issued_country)) &&
      secRow.date &&
      secRow.firstname &&
      secRow.lastname &&
      secRow.title &&
      (secRow.type === 'local' ? secRow.province : true) &&
      (secRow.type === 'local' ? secRow.district : true) &&
      (secRow.type === 'local' ? secRow.city : true) &&
      (secRow.type === 'local' ? secRow.postcode : true) &&
      (secRow.type === 'foreign' ? secRow.forProvince : true) &&
      (secRow.type === 'foreign' ? secRow.forCity : true) &&
      (secRow.type === 'foreign' ? ((this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS') ? true : secRow.forPostcode) : true) &&
      secRow.mobile && this.phonenumber(secRow.mobile, secRow.type) &&
      secRow.email && this.validateEmail(secRow.email) &&
      (secRow.type === 'local' ? secRow.localAddress1 : true) &&
      (secRow.type === 'foreign' ? secRow.forAddress1 : true) &&
      ((secRow.nic && secRow.regDate) ? this.validateRegNo(secRow.nic, secRow.regDate, 'sec') : true) &&
      ((this.companyTypeKey === 'COMPANY_TYPE_PUBLIC' || this.companyTypeKey === 'COMPANY_TYPE_GUARANTEE_32' || this.companyTypeKey === 'COMPANY_TYPE_GUARANTEE_34') ? secRow.regDate : true) &&
      ((secRow.isShareholderEdit === undefined || secRow.isShareholderEdit === false) || secRow.shareTypeEdit === 'single' && secRow.noOfSingleSharesEdit ||
        secRow.shareTypeEdit === 'core' && secRow.coreGroupSelectedEdit ||
        secRow.shareTypeEdit === 'core' && (secRow.coreShareGroupNameEdit && secRow.coreShareValueEdit)
      )
    )) {
      this.enableStep2Submission = false;
      this.enableStep2SubmissionEdit = false;
      this.secList.secs[rowId]['validEdit'] = false;
      return false;
    } else {
      if (secRow.isReg) {
        if (!secRow.regDate) {
          this.enableStep2Submission = false;
          this.enableStep2SubmissionEdit = false;
          this.secList.secs[rowId]['validEdit'] = false;
          return false;
        } else {
          this.enableStep2Submission = true;
          this.enableStep2SubmissionEdit = true;
          this.secList.secs[rowId]['validEdit'] = true;
          return true;
        }
      } else {
        this.enableStep2Submission = true;
        this.enableStep2SubmissionEdit = true;
        this.secList.secs[rowId]['validEdit'] = true;
        return true;
      }
    }
  }

  validateSecEditForOldSecFirm(rowId) {
    let secRow = this.oldSecFirmList.secs[rowId];
    if (!(
      ((this.compayType.value === 'Public') ? secRow.pvNumber : true) &&
      secRow.firm_name &&
      secRow.firm_province &&
      // secRow.firm_district &&
      ((secRow.secType === 'firm' && !(this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS')) ? secRow.firm_district : true) &&
      secRow.firm_city &&
      secRow.firm_localAddress1 &&
      secRow.firm_postcode &&
      secRow.firm_date_change &&
      (secRow.firm_mobile && this.phonenumber(secRow.firm_mobile, secRow.type)) &&
      (secRow.firm_email && this.validateEmail(secRow.firm_email) &&
        ((secRow.secType === 'firm' && secRow.type !== 'local' && (this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS')) ? secRow.firm_country : true)
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
      this.enableStep2SubmissionOldSecFirmEdit = false;
      this.oldSecFirmList.secs[rowId]['validEdit'] = false;
      return false;
    } else {
      if (secRow.isReg) {
        if (!secRow.regDate) {
          this.enableStep2Submission = false;
          this.enableStep2SubmissionOldSecFirmEdit = false;
          this.oldSecFirmList.secs[rowId]['validEdit'] = false;
          return false;
        } else {
          this.enableStep2Submission = true;
          this.enableStep2SubmissionOldSecFirmEdit = true;
          this.oldSecFirmList.secs[rowId]['validEdit'] = true;
          return true;
        }
      } else {
        this.enableStep2Submission = true;
        this.enableStep2SubmissionOldSecFirmEdit = true;
        this.oldSecFirmList.secs[rowId]['validEdit'] = true;
        return true;
      }
    }
  }
  validateSecEditForSecFirm(rowId) {
    let secRow = this.secFirmList.secs[rowId];
    if (!(
      ((this.compayType.value === 'Public') ? secRow.pvNumber : true) &&
      secRow.firm_name &&
      secRow.firm_province &&
      // secRow.firm_district &&
      ((secRow.secType === 'firm' && !(this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS')) ? secRow.firm_district : true) &&
      secRow.firm_city &&
      secRow.firm_localAddress1 &&
      secRow.firm_postcode &&
      secRow.firm_date &&
      (secRow.firm_mobile && this.phonenumber(secRow.firm_mobile, secRow.type)) &&
      (secRow.firm_email && this.validateEmail(secRow.firm_email) &&
        ((secRow.secType === 'firm' && secRow.type !== 'local' && (this.compayType.key === 'COMPANY_TYPE_OFFSHORE' || this.compayType.key === 'COMPANY_TYPE_OVERSEAS')) ? secRow.firm_country : true)
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
      this.secFirmList.secs[rowId]['validEdit'] = false;
      return false;
    } else {
      if (secRow.isReg) {
        if (!secRow.regDate) {
          this.enableStep2Submission = false;
          this.enableStep2SubmissionEdit = false;
          this.secFirmList.secs[rowId]['validEdit'] = false;
          return false;
        } else {
          this.enableStep2Submission = true;
          this.enableStep2SubmissionEdit = true;
          this.secFirmList.secs[rowId]['validEdit'] = true;
          return true;
        }
      } else {
        this.enableStep2Submission = true;
        this.enableStep2SubmissionEdit = true;
        this.secFirmList.secs[rowId]['validEdit'] = true;
        return true;
      }
    }
  }
  getProvincesForStakeHolderEdit(type, i) {
    let provinces = Object.assign({}, this.provinces);
    let filterProvince: Array<IProvince> = [];
    for (let j in provinces) {
      filterProvince.push(provinces[j]);
    }
    if (type === 'director') {
      this.directorList.directors[i].screen1Provinces = filterProvince;
      this.validateDirectorEdit(i);
    }
    if (type === 'olddirector') {
      this.oldDirectorList.directors[i].screen1Provinces = filterProvince;
      this.validateOldDirectorEdit(i);
    }
    if (type === 'sec') {
      this.secList.secs[i].screen1Provinces = filterProvince;
      this.validateSecEdit(i);
    }
    if (type === 'oldsec') {
      this.oldSecList.secs[i].screen1Provinces = filterProvince;
      this.validateOldSecEdit(i);
    }
    if (type === 'secFirm') {
      this.secFirmList.secs[i].screen1Provinces = filterProvince;
      this.validateSecEditForSecFirm(i);
    }
    if (type === 'oldsecFirm') {
      this.oldSecFirmList.secs[i].screen1Provinces = filterProvince;
      this.validateSecEditForOldSecFirm(i);
    }
  }
  getDistrictsForStakeholderEdit(type, i, provinceName, load = false) {
    let districts = Object.assign({}, this.districts);
    let filterDistricts: Array<IDistrict> = [];
    for (let j in districts) {
      if (districts[j].provinceName === provinceName) {
        filterDistricts.push(districts[j]);
      }
    }
    if (type === 'director') {
      this.directorList.directors[i].screen1Districts = filterDistricts;
      if (load === false) {
        this.directorList.directors[i].city = '';
        this.directorList.directors[i].district = '';
      }
      this.validateDirectorEdit(i);
    }
    if (type === 'olddirector') {
      this.oldDirectorList.directors[i].screen1Districts = filterDistricts;
      if (load === false) {
        this.oldDirectorList.directors[i].city = '';
        this.oldDirectorList.directors[i].district = '';
      }
      this.validateOldDirectorEdit(i);
    }
    if (type === 'sec') {
      this.secList.secs[i].screen1Districts = filterDistricts;
      if (load === false) {
        this.secList.secs[i].city = '';
        this.secList.secs[i].district = '';
        this.secList.secs[i].firm_city = '';
        this.secList.secs[i].firm_district = '';
      }
      this.validateSecEdit(i);
    }
    if (type === 'oldsec') {
      this.oldSecList.secs[i].screen1Districts = filterDistricts;
      if (load === false) {
        this.oldSecList.secs[i].city = '';
        this.oldSecList.secs[i].district = '';
        this.oldSecList.secs[i].firm_city = '';
        this.oldSecList.secs[i].firm_district = '';
      }
      this.validateOldSecEdit(i);
    }
    if (type === 'secFirm') {
      this.secFirmList.secs[i].screen1Districts = filterDistricts;
      if (load === false) {
        this.secFirmList.secs[i].firm_city = '';
        this.secFirmList.secs[i].firm_district = '';
      }
      this.validateSecEditForSecFirm(i);
    }
    if (type === 'oldsecFirm') {
      this.oldSecFirmList.secs[i].screen1Districts = filterDistricts;
      if (load === false) {
        this.oldSecFirmList.secs[i].firm_city = '';
        this.oldSecFirmList.secs[i].firm_district = '';
      }
      this.validateSecEditForOldSecFirm(i);
    }
  }
  getCitiesForStakeholderEdit(type, i, districtName, load = false) {
    let cities = Object.assign({}, this.cities);
    let filterCities: Array<ICity> = [];
    for (let j in cities) {
      if (cities[j].districtName === districtName) {
        filterCities.push(cities[j]);
      }
    }
    if (type === 'director') {
      this.directorList.directors[i].screen1Cities = filterCities;
      if (load === false) {
        this.directorList.directors[i].city = '';
      }
      this.validateDirectorEdit(i);
    }
    if (type === 'olddirector') {
      this.oldDirectorList.directors[i].screen1Cities = filterCities;
      if (load === false) {
        this.oldDirectorList.directors[i].city = '';
      }
      this.validateOldDirectorEdit(i);
    }
    if (type === 'sec') {
      this.secList.secs[i].screen1Cities = filterCities;
      if (load === false) {
        this.secList.secs[i].city = '';
        this.secList.secs[i].firm_city = '';
      }
      this.validateSecEdit(i);
    }
    if (type === 'oldsec') {
      this.oldSecList.secs[i].screen1Cities = filterCities;
      if (load === false) {
        this.oldSecList.secs[i].city = '';
        this.oldSecList.secs[i].firm_city = '';
      }
      this.validateOldSecEdit(i);
    }
    if (type === 'secFirm') {
      this.secFirmList.secs[i].screen1Cities = filterCities;
      if (load === false) {
        this.secFirmList.secs[i].firm_city = '';
      }
      this.validateSecEditForSecFirm(i);
    }
    if (type === 'oldsecFirm') {
      this.oldSecFirmList.secs[i].screen1Cities = filterCities;
      if (load === false) {
        this.oldSecFirmList.secs[i].firm_city = '';
      }
      this.validateSecEditForOldSecFirm(i);
    }
  }

  calculatePayment() {
    this.totalFormCost = (this.form18Cost * this.directorList.directors.length) + (this.form19Cost * (this.secList.secs.length + this.secFirmList.secs.length)) + (this.form20Cost);
  }

  goToNext(){
    let x = 0;
    let y = 0;
    let z = 0;
    let a = 0;
    let b = 0;
    let c = 0;
    let d = 0;
    let e = 0;
    let f = 0;
    let p = this.directorList.directors.length;
    let r = this.secList.secs.length;
    let q = this.secFirmList.secs.length;

    //// collecting nics
    let dNicarray = [];
    let sNicarray = [];
    for (let director of this.directorList.directors) {
      dNicarray.push(director.nic);
    }
    for (let director of this.oldDirectorList.directors) {
      if (director.isdeleted === null ) {
        dNicarray.push(director.nic);
      }
    }
    for (let sec of this.secList.secs) {
      sNicarray.push(sec.nic);
    }
    for (let sec of this.oldSecList.secs) {
      if (sec.isdeleted === null ) {
        sNicarray.push(sec.nic);
      }
    }


    // counting changes
    for (let director of this.oldDirectorList.directors) {
      if (director.newid) {
        a = a + 1;
      }
      else {
        continue;
      }
    }
    for (let sec of this.oldSecList.secs) {
      if (sec.newid) {
        b = b + 1;
      }
      else {
        continue;
      }
    }
    for (let sec of this.oldSecFirmList.secs) {
      if (sec.newid) {
        c = c + 1;
      }
      else {
        continue;
      }
    }
    // end of counting changes

    for (let director of this.oldDirectorList.directors) {
      if (director.isdeleted === null ) {
        x = x + 1;
      }
      else {
        d = d + 1;
        continue;
      }
    }
    for (let sec of this.oldSecList.secs) {
      if (sec.isdeleted === null ) {
        y = y + 1;
      }
      else {
        e = e + 1;
        continue;
      }
    }
    for (let sec of this.oldSecFirmList.secs) {
      if (sec.isdeleted === null ) {
        z = z + 1;
      }
      else {
        f = f + 1;
        continue;
      }
    }
    let changes = a + b + c;
    let deletes = d + e + f;
    let newadds = p + q + r;
    let dirTotal = x + p;
    let secTotal = y + r + q + z;
    if (this.companyTypeKey === 'COMPANY_TYPE_GUARANTEE_32' ||
        this.companyTypeKey === 'COMPANY_TYPE_GUARANTEE_34' ||
        this.companyTypeKey === 'COMPANY_TYPE_PUBLIC') {
          if (dirTotal >= 2 && secTotal > 0 && (changes > 0 || newadds > 0 || deletes > 0)) {
            this.enableGoToDownload = true;
            console.log('111111111');
          }
          else{
            this.enableGoToDownload = false;
          //  this.alertService.warning('Not enough directors and secretaries');
            console.log('22222222222');
          }
        }
     else if (this.companyTypeKey === 'COMPANY_TYPE_PRIVATE' ||
     this.companyTypeKey === 'COMPANY_TYPE_UNLIMITED') {
      if (dirTotal === 1 && secTotal === 1 && (changes > 0 || newadds > 0 || deletes > 0)) {
        if (sNicarray.includes(dNicarray[0])) {
          this.enableGoToDownload = false;
         // this.alertService.warning('Not enough directors and secretaries');
          console.log('1or1');
        }
        else{
          this.enableGoToDownload = true;
          console.log('1no1');
        }
      }
      else if (((dirTotal > 1 && secTotal > 1) || (dirTotal > 1 && secTotal === 1) || (dirTotal === 1 && secTotal > 1)) && (changes > 0 || newadds > 0 || deletes > 0)) {
        this.enableGoToDownload = true;
        console.log('333333333');
      }
      else{
        this.enableGoToDownload = false;
       // this.alertService.warning('Not enough directors and secretaries');
        console.log('44444444');
      }
     }
  }

  __validateMembers() {
    if ((this.oldDirectorList.directors.length > 0) && (this.oldSecList.secs.length > 0)) {
      this.enableGoToDownload = true;
    } else if ((this.oldDirectorList.directors.length > 0) && (this.oldSecList.secs.length === 0)) {
      if (this.secList.secs.length > 0 || this.secFirmList.secs.length > 0) {
        this.enableGoToDownload = true;
      } else {
        this.enableGoToDownload = false;
      }
    } else if ((this.oldDirectorList.directors.length === 0) && (this.oldSecList.secs.length > 0)) {
      if (this.directorList.directors.length > 0) {
        this.enableGoToDownload = true;
      } else {
        this.enableGoToDownload = false;
      }
    } else if ((this.oldDirectorList.directors.length === 0) && (this.oldSecList.secs.length === 0)) {
      if ((this.directorList.directors.length > 0) && (this.secList.secs.length > 0 || this.secFirmList.secs.length > 0)) {
        this.enableGoToDownload = true;
      } else {
        this.enableGoToDownload = false;
      }
    }
  }




  resubmit() {
    const data = {
      companyId: this.companyId,
    };
    this.spinner.show();

    this.memberChange.memberResubmit(data)
      .subscribe(
        req => {
         // this.loadData();
          if (req['status']) {
            this.spinner.hide();
            this.resubmitSuccess = true;
            return false;
          }else {
            this.loadMemberData(this.companyId, this.loggedinUserEmail);
            this.resubmitSuccess = false;
            alert( req['message']);
          }
        },
        error => {
          this.spinner.hide();
          this.resubmitSuccess = false;
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
