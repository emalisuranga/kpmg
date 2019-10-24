import { ICompanyCommentWith } from './../../../../../http/models/recervationdata.model';
import { NgxSpinnerService } from 'ngx-spinner';
import { HelperService } from '../../../../../http/shared/helper.service';
import { GeneralService } from '../../../../../http/services/general.service';
import { NameResarvationService } from '../../../../../http/services/name-resarvation.service';
import { INames } from '../../../../../http/models/recervationdata.model';
import { DebenturesDataService } from '../issue-of-debentures/debentures-data.service';
import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { DomSanitizer } from '@angular/platform-browser';
import { RoleGuard } from 'src/app/http/guards/role-guard';
import { environment } from '../../../../../../environments/environment';
import { DataService } from '../../../../../storage/data.service';
import { IirdInfo } from '../../../../../http/models/incorporation.model';
import { AuthService } from '../../../../../http/shared/auth.service';

@Component({
  selector: 'app-company-card',
  templateUrl: './company-card.component.html',
  styleUrls: ['./company-card.component.scss']
})
export class CompanyCardComponent implements OnInit, OnDestroy {
  companyId: string;
  id: number;
  private sub: any;
  public name: INames;
  storage1: any;
  ACstorage: any;
  RRstorage: any;
  BDstorage: any;
  fileUrl;
  addresschanges = [];
  debentureChangedetails = [];
  Acaddresschanges = [];
  RRaddresschanges = [];
  satisChargechanges = [];
  bsdchanges = [];
  registerOfChargesRecord = [];
  chargesRegistrationRecord = [];
  callShareDetails = [];
  annualReturnDetails = [];
  directorSecChangeDetails = [];
  statedCapital = [];
  overseasNameChangeNotice = [];
  appintAdminDetails = [];
  noticeDetails = [];
  overseaseAltDetails = [];
  offshoreAltDetails = [];
  form9Details = [];
  Sharesstorage: any;
  bsdtype: any;
  issueofshares = [];
  prospectusDetails = [];
  otherscourtorder = [];
  priorApproval = [];
  annualAccountsDetails = [];
  specialResolutionDetails = [];
  affairs = [];
  public comments: Array<ICompanyCommentWith> = [];
  memberRegister = false;
  blockBackToForm = false;

  stakeholderKey = '';

  showIncompleteActions = environment.showIncompleteActions;




  ird: IirdInfo = {
    commencementdate: '',
    bac: '',
    preferredlanguage: null,
    preferredmodeofcommunication: null,
    isboireg: false,
    boistartdate: '',
    boienddate: '',
    companysalutation: '',
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
    mobile: '',
    office: '',
    email: '',
    contactpersonname: '',
    id: null,
    taxpayer_identification_number: '',
    rejected_resion: '',
    status: null

  };
  irdCount = 0;

  constructor(
    private helper: HelperService,
    private route: ActivatedRoute,
    private general: GeneralService,
    private spinner: NgxSpinnerService,
    private dataservice: DataService,
    public gard: RoleGuard,
    private reservationService: NameResarvationService,
    private router: Router,
    private DebData: DebenturesDataService,
    private Auth: AuthService) {
    this.companyId = route.snapshot.paramMap.get('id');
  }

  ngOnInit() {
    this.spinner.show();
    this.sub = this.route.params.subscribe(params => {
      this.reservationService.getNameReservationData(params['id'])
        .subscribe(
          req => {
            this.name = req['companyInfor'];
            this.name.documents = req['companyDocument'];
            this.id = params['id'];
            this.comments = req['comments'];
            this.addresschanges = req['changedetails'];
            this.issueofshares = req['sharesChangedetails'];
            this.debentureChangedetails = req['debentureChangedetails'];
            this.Acaddresschanges = req['acadchangedetails'];
            this.RRaddresschanges = req['rradchangedetails'];
            this.satisChargechanges = req['satischangedetails'];
            this.otherscourtorder = req['otherscourtorder'];
            // this.priorApproval = req['priorApproval'];
            this.affairs = req['affairs'];
            // console.log(this.otherscourtorder);
            this.bsdchanges = req['bsdchangedetails'];
            if ((this.name.typeKey === 'COMPANY_TYPE_GUARANTEE_32') || (this.name.typeKey === 'COMPANY_TYPE_GUARANTEE_34')) {
              this.memberRegister = true;
            }
            else{
              this.memberRegister = false;
            }
            this.registerOfChargesRecord = req['regChargesDetails'];
            this.callShareDetails = req['callShareDetails'];
            this.annualReturnDetails = req['annualReturnDetails'];
            this.directorSecChangeDetails = req['directorSecChangeDetails'];
            this.chargesRegistrationRecord = req['chargesRegistratioRecord'];
            this.statedCapital = req['statedCapitalDetails'];
            this.overseasNameChangeNotice = req['overseasNameChangeNotice'];
            this.appintAdminDetails = req['appintAdminDetails'];
            this.noticeDetails = req['noticeDetails'];
            this.overseaseAltDetails = req['overseaseAltDetails'];
            this.offshoreAltDetails = req['offshoreAltDetails'];
            this.form9Details = req['form9Details'];
            this.prospectusDetails = req['prospectusDetails'];
            this.annualAccountsDetails = req['annualAccountsDetails'];
            this.specialResolutionDetails = req['specialResolutionDetails'];

            this.stakeholderKey = req['stakeholder_key'];

            /***ird info */

            this.ird.taxpayer_identification_number = req['irdTIN'];
            this.ird.rejected_resion = req['irdRejectMessage'];
            this.ird.status = req['irdStatus'];


            /**end ird info */

            this.spinner.hide();
          }

        );
    });
    // console.log(this.id);
  }

  ngOnDestroy() {
    this.sub.unsubscribe();
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

  getCompanyChangeAddress() {
    localStorage.setItem('companyId', JSON.stringify(this.id));
    localStorage.setItem('status', JSON.stringify('new'));
    this.router.navigate(['/dashboard/companyaddresschange']);
  }

  getRRChangeAddress() {
    localStorage.setItem('RRcompanyId', JSON.stringify(this.id));
    localStorage.setItem('RRtype', JSON.stringify(this.memberRegister));
    localStorage.setItem('RRstatus', JSON.stringify('new'));
    this.router.navigate(['/dashboard/companyrrchange']);
  }

  continueRegistrationForRRAddress() {
    if (this.RRaddresschanges[0]['setKey'] === 'RECORDS_REGISTER_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT') {

      this.RRstorage = {
        comId: this.id,
        changeReqId: this.RRaddresschanges[0]['id'],
        request_type: this.RRaddresschanges[0]['request_type'],
        comType: this.memberRegister
      };

      localStorage.setItem('RRstorage', JSON.stringify(this.RRstorage));
      this.router.navigate(['/dashboard/companyrrchangeresubmit']);
    }
    else if (this.RRaddresschanges[0]['setKey'] === 'RECORDS_REGISTER_ADDRESS_CHANGE_PROCESSING') {

      this.RRstorage = {
        comId: this.id,
        changeReqId: this.RRaddresschanges[0]['id'],
        request_type: this.RRaddresschanges[0]['request_type'],
        comType: this.memberRegister
      };
      localStorage.setItem('RRstatus', JSON.stringify('processing'));
      localStorage.setItem('RRcompanyId', JSON.stringify(this.id));
      localStorage.setItem('RRstorage', JSON.stringify(this.RRstorage));
      this.router.navigate(['/dashboard/companyrrchange']);
    }
  }

  getChangeAddress() {
    localStorage.setItem('ACcompanyId', JSON.stringify(this.id));
    localStorage.setItem('ACstatus', JSON.stringify('new'));
    this.router.navigate(['/dashboard/companyaccountingaddresschange']);
  }

  getCompanyBalDateChange() {
    localStorage.setItem('BDcompanyId', JSON.stringify(this.id));
    localStorage.setItem('BDstatus', JSON.stringify('new'));
    this.router.navigate(['/dashboard/companybdchange']);
  }

  continueRegistration() {
    if (this.addresschanges[0]['setKey'] === 'COMPANY_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT') {

      this.storage1 = {
        comId: this.id,
        changeid: this.addresschanges[0]['id'],
        newaddressid: this.addresschanges[0]['new_address_id'],
        date: this.addresschanges[0]['address_effect_on_date']
      };

      localStorage.setItem('storage1', JSON.stringify(this.storage1));
      this.router.navigate(['/dashboard/companyaddresschangeresubmit']);
    }
    else if (this.addresschanges[0]['setKey'] === 'COMPANY_ADDRESS_CHANGE_PROCESSING') {

      this.storage1 = {
        comId: this.id,
        changeid: this.addresschanges[0]['id'],
        newaddressid: this.addresschanges[0]['new_address_id'],
        date: this.addresschanges[0]['address_effect_on_date']
      };
      localStorage.setItem('status', JSON.stringify('processing'));
      localStorage.setItem('companyId', JSON.stringify(this.id));
      localStorage.setItem('storage1', JSON.stringify(this.storage1));
      this.router.navigate(['/dashboard/companyaddresschange']);
    }
  }

  // Issue of Shares
  issueofSharesRegistration() {
    if (this.issueofshares) {
      localStorage.setItem('companyId', JSON.stringify(this.id));
      localStorage.setItem('status', JSON.stringify('newbuthaspreviousrecord'));
      localStorage.setItem('preChangeReqID', JSON.stringify(this.issueofshares['changeReqID']));
      this.router.navigate(['/dashboard/shares']);
    } else {
      localStorage.setItem('companyId', JSON.stringify(this.id));
      localStorage.setItem('status', JSON.stringify('new'));
      this.router.navigate(['/dashboard/shares']);
    }
  }

  continueIssueofSharesRegistration(setKey) {
    if (setKey === 'COMPANY_ISSUE_OF_SHARES_REQUEST_TO_RESUBMIT') {
      this.Sharesstorage = {
        companyId: this.id,
        changeReqID: this.issueofshares['changeReqID'],
        status: 'resubmit'
      };
      localStorage.setItem('companyId', JSON.stringify(this.id));
      localStorage.setItem('status', JSON.stringify('resubmit'));
      localStorage.setItem('changeReqID', JSON.stringify(this.issueofshares['changeReqID']));
      localStorage.setItem('Sharesstorage', JSON.stringify(this.Sharesstorage));
      this.router.navigate(['/dashboard/sharesresubmit']);
    }
    else if (setKey === 'COMPANY_ISSUE_OF_SHARES_PROCESSING') {
      this.Sharesstorage = {
        companyId: this.id,
        changeReqID: this.issueofshares['changeReqID'],
        status: 'processing'
      };
      console.log(this.Sharesstorage);
      localStorage.setItem('companyId', JSON.stringify(this.id));
      localStorage.setItem('status', JSON.stringify('processing'));
      localStorage.setItem('changeReqID', JSON.stringify(this.issueofshares['changeReqID']));
      localStorage.setItem('Sharesstorage', JSON.stringify(this.Sharesstorage));
      this.router.navigate(['/dashboard/shares']);
    }

  }

  continueRegistrationForBsDate() {
    if (this.bsdchanges[0]['setKey'] === 'BALANCE_SHEET_DATE_CHANGE_REQUEST_TO_RESUBMIT') {

      this.BDstorage = {
        comId: this.id,
        changeReqId: this.bsdchanges[0]['id'],
        request_type: this.bsdchanges[0]['request_type'],
        bsdid: this.bsdchanges[0]['bsdid'],
        bsdchangeid: this.bsdchanges[0]['bsdchangeid'],
      };

      localStorage.setItem('BDstorage', JSON.stringify(this.BDstorage));
      this.router.navigate(['/dashboard/companybdchangeresubmit']);
    }
    else if (this.bsdchanges[0]['setKey'] === 'BALANCE_SHEET_DATE_CHANGE_PROCESSING') {

      this.BDstorage = {
        comId: this.id,
        changeReqId: this.bsdchanges[0]['id'],
        request_type: this.bsdchanges[0]['request_type'],
        bsdid: this.bsdchanges[0]['bsdid'],
        bsdchangeid: this.bsdchanges[0]['bsdchangeid'],
      };
      localStorage.setItem('BDstatus', JSON.stringify('processing'));
      localStorage.setItem('BDcompanyId', JSON.stringify(this.id));
      localStorage.setItem('BDstorage', JSON.stringify(this.BDstorage));
      this.router.navigate(['/dashboard/companybdchange']);
    }
  }

  satisChargeChange() {
    this.router.navigate(['dashboard/companymemo', this.companyId]);
  }

  continueRegistrationForSatisCharge() {
    if (this.satisChargechanges[0]['setKey'] === 'COMPANY_CHANGE_REQUEST_TO_RESUBMIT') {
      this.router.navigate(['/dashboard/companymemoresubmit', this.companyId]);
    }
  }

  // Issue of Debentures
  getIssueofDebentures() {
    if (this.debentureChangedetails) {
      this.DebData.setComId(this.id);
      this.DebData.setReqId(this.debentureChangedetails['changeReqID']);
      this.DebData.setStatus('newbuthaspreviousrecord');
      this.DebData.setNavigatetoDashboard(true);
      this.router.navigate(['/dashboard/issueofdebentures']);
    } else {
      this.DebData.setComId(this.id);
      this.DebData.setStatus('new');
      this.DebData.setNavigatetoDashboard(true);
      this.router.navigate(['/dashboard/issueofdebentures']);
    }
  }

  continueDebentureRegistration(setKey, changeReqID) {
    if (setKey === 'COMPANY_DEBENTURES_REQUEST_TO_RESUBMIT') {

      this.DebData.setComId(this.id);
      this.DebData.setReqId(changeReqID);
      this.DebData.setStatus('resubmit');
      this.DebData.setNavigatetoDashboard(true);
      this.router.navigate(['/dashboard/issueofdebenturesresubmit']);
    }
    else if (setKey === 'COMPANY_DEBENTURES_PROCESSING') {

      this.DebData.setComId(this.id);
      this.DebData.setReqId(changeReqID);
      this.DebData.setStatus('processing');
      this.DebData.setNavigatetoDashboard(true);
      this.router.navigate(['/dashboard/issueofdebentures']);
    }
  }
  directorSecretaryChange() {
    this.router.navigate(['dashboard/directorsecretarychange', this.companyId]);
  }

  annualReturn() {
    this.router.navigate(['dashboard/annual-return', this.companyId]);
  }
  registerOfCharges() {
    this.router.navigate(['dashboard/register-of-charges', this.companyId]);
  }

  chargesRegistration() {
    this.router.navigate(['dashboard/charges-registration', this.companyId]);
  }

  callsOnShares() {
    this.router.navigate(['dashboard/calls-on-shares', this.companyId]);
  }

  appointOfAdmin() {
    this.router.navigate(['dashboard/appointment-of-administrator', this.companyId]);
  }

  continueRegistrationForAcAddress() {
    if (this.Acaddresschanges[0]['setKey'] === 'ACCOUNTING_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT') {

      this.ACstorage = {
        comId: this.id,
        changeReqId: this.Acaddresschanges[0]['id'],
        request_type: this.Acaddresschanges[0]['request_type']
      };

      localStorage.setItem('ACstorage', JSON.stringify(this.ACstorage));
      this.router.navigate(['/dashboard/companyaccountingaddresschangeresubmit']);
    }
    else if (this.Acaddresschanges[0]['setKey'] === 'ACCOUNTING_ADDRESS_CHANGE_PROCESSING') {

      this.ACstorage = {
        comId: this.id,
        changeReqId: this.Acaddresschanges[0]['id'],
        request_type: this.Acaddresschanges[0]['request_type']
      };
      localStorage.setItem('ACstatus', JSON.stringify('processing'));
      localStorage.setItem('ACcompanyId', JSON.stringify(this.id));
      localStorage.setItem('ACstorage', JSON.stringify(this.ACstorage));
      this.router.navigate(['/dashboard/companyaccountingaddresschange']);
    }
  }

  getCapitalSated() {
   // this.dataservice.setId(this.companyId);
    this.router.navigate(['/dashboard/reduction-of-capital/', this.companyId ]);
  }

  getOverseasNameChangeNotice() {
    this.router.navigate(['dashboard/notice-of-name-change-of-overseas-company', this.companyId]);
  }

  getNoticeDetails() {
    this.router.navigate(['dashboard/company-notice', this.companyId]);
  }

  getOversaseAltDetails() {
    this.router.navigate(['dashboard/alterations-of-overseas-company', this.companyId]);
  }

  getOffshoreAltDetails() {
    this.router.navigate(['dashboard/alterations-of-offshore-company', this.companyId]);
  }

  getForm9Details() {
    this.router.navigate(['dashboard/shares-redemption-acquisition', this.companyId]);
  }


  getProspectusDetails() {
    this.router.navigate(['dashboard/prospectus-registration', this.companyId]);
  }

  othersCourtOrder(status){
    // localStorage.setItem('otherCompanyId', JSON.stringify(this.id));
    // this.router.navigate(['dashboard/othersCourtOrder', this.companyId]);
    this.router.navigate(['dashboard/othersCourtOrder/' + this.companyId + '/' + status]);
  }

  getPriorApproval() {
    // localStorage.setItem('otherCompanyId', JSON.stringify(this.id));
    this.router.navigate(['dashboard/ListApproval', this.companyId]);
  }

  getAnnualAccountDetails() {
    this.router.navigate(['dashboard/annual-accounts', this.companyId]);
  }

  getStatementOfAffairs() {
    this.router.navigate(['dashboard/Affairs', this.companyId]);
  }

  getSpecialResolutionDetails() {
    this.router.navigate(['dashboard/special-resolution', this.companyId]);
  }

  getIssueOfSharesDetails() {
    this.router.navigate(['dashboard/issue-of-shares', this.companyId]);
  }

  areYouSureYes() {
    this.blockBackToForm = true;
  }
  areYouSureNo() {
    this.blockBackToForm = false;
  }

}
