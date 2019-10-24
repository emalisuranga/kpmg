import * as $ from 'jquery';
import { ToastrService } from 'ngx-toastr';
import { NgxSpinnerService } from 'ngx-spinner';
import { HttpClient } from '@angular/common/http';
import { HttpHeaders } from '@angular/common/http';
import { ModalDirective } from 'angular-bootstrap-md';
import { Router, ActivatedRoute } from '@angular/router';
import { DomSanitizer } from '@angular/platform-browser';
import { AuditorDataService } from '../auditor-data.service';
import { DataService } from '../../../../../../storage/data.service';
import { Item, IBuy } from './../../../../../../http/models/payment';
import { IBuyDetails } from './../../../../../../storage/ibuy-details';
import { Component, OnInit, ViewChild, AfterViewInit, HostListener } from '@angular/core';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { AuditorService } from '../../../../../../http/services/auditor.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { CalculationService } from './../../../../../../http/shared/calculation.service';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { IAuditorData, IAuditorDataFirm } from '../../../../../../http/models/auditor.model';
import { environment } from '../../../../../../../environments/environment';
declare var google: any;

@Component({
  selector: 'app-register-auditor-firm',
  templateUrl: './register-auditor-firm.component.html',
  styleUrls: ['./register-auditor-firm.component.scss']
})
export class RegisterAuditorFirmComponent implements OnInit, AfterViewInit {

  @ViewChild('slModal') public slModal: ModalDirective;
  @ViewChild('nonslModal') public nonslModal: ModalDirective;

  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;

  auditorDetails: IAuditorData = {
    registeredUser: false, nic: '', passport: '', loggedInUser: '', id: 0, title: '', sinFullName: '', tamFullName: '', sinAd: '', tamAd: '',
    firstname: '', lastname: '', residentialLocalAddress1: '', residentialLocalAddress2: '', residentialProvince: null, residentialDistrict: null,
    residentialCity: null, rgnDivision: null, residentialPostCode: '', businessName: '', businessLocalAddress1: '', businessLocalAddress2: '',
    businessProvince: '', businessDistrict: '', businessCity: '', gnDivision: '', businessPostCode: '', birthDay: '',
    pQualification: '', nationality: '', race: '', whereDomiciled: '', dateTakeResidenceInSrilanka: '',
    dateConResidenceInSrilanka: '', ownedProperty: '', otherFacts: '',
    isUnsoundMind: '', isInsolventOrBankrupt: '', reason1: '', isCompetentCourt: '', reason2: '', otherDetails: '', subClauseQualified: '', isExistAud: '', certificateNo: '',
  };

  auditorFirmDetails: IAuditorDataFirm = {
    firmId: 0, firmName: '', businessLocalAddress1: '', businessLocalAddress2: '', businessProvince: undefined, sinFirmName: '', tamFirmName: '', sinFirmAd: '', tamFirmAd: '',
    businessDistrict: undefined, businessCity: undefined, gnDivision: undefined, businessPostCode: '', loggedInUser: '', firmPartner: '', isExistAud: '', certificateNo: '', qualification: ''
  };

  nic: string;
  passport: string;
  loggedinUserEmail: string;
  otherState: string;
  firmId: number;
  enableNic = false;
  enableGoToPay = false;
  enableStep1Submission = false;
  blockBackToForm = false;
  blockPayment = false;

  application = [];
  regCertificateUploadList = [];
  auditorFirmPartnerDetails = [];
  auditorIDs = [];
  description: string;
  cipher_message: string;

  businessProvince: string;
  businessDistrict: string;
  businessCity: string;
  bgnDivision: string;

  qualification: string;

  sinFullName = null;
  tamFullName = null;

  sinFirmName = null;
  tamFirmName = null;
  tamilelements;
  sinElements;

  sinAd = null;
  tamAd = null;

  email: any;
  mobile: any;
  tel: any;

  sinFirmAd = null;
  tamFirmAd = null;
  adtamilelements;
  adsinElements;

  stepOn = 0;
  processStatus: string;
  progress = {
    stepArr: [
      { label: 'Add Partners', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },
    ],
    progressPercentage: '12.5%'
  };

  constructor(
    public data: DataService,
    private helper: HelperService,
    private auditorService: AuditorService,
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private general: GeneralService,
    private AudData: AuditorDataService,
    private crToken: PaymentService,
    private snotifyService: ToastrService,
    private iBy: IBuyDetails,
    public calculation: CalculationService,
  ) {
    this.firmId = this.AudData.getFirmId; // for continue upload process after canceled...

    if (!(this.firmId === undefined)) {
      this.loadAuditorFirmData(this.firmId);
      this.getAuditorID(this.firmId);
      this.loadUploadedFile(this.firmId);
      this.changeProgressStatuses(1);
      this.AudData.firmId = undefined;
    }

    this.loggedinUserEmail = localStorage.getItem('currentUser');
    this.loggedinUserEmail = this.loggedinUserEmail.replace(/^"(.*)"$/, '$1');

  }


  ngOnInit() {
    document.getElementById('div1').style.display = 'block';
    document.getElementById('div2').style.display = 'none';
    document.getElementById('div3').style.display = 'none';
    this.sinElements = document.getElementsByClassName('sinhalaname');
    this.tamilelements = document.getElementsByClassName('tamilname');
    this.adsinElements = document.getElementsByClassName('adsinhalaname');
    this.adtamilelements = document.getElementsByClassName('adtamilname');
  }

  show5() {
    document.getElementById('div3').style.display = 'none';
    this.auditorFirmDetails['certificateNo'] = '';
  }
  show6() {
    document.getElementById('div3').style.display = 'block';
  }

  ngAfterViewInit() {
    $('.auditor-type-tab-wrapper .tab').on('click', function () {
      let self = $(this);
      $('.auditor-type-tab-wrapper .tab').removeClass('active');
      $(this).addClass('active');
    });
  }
  sriLankan() {
    document.getElementById('div1').style.display = 'block';
    document.getElementById('div2').style.display = 'none';
  }
  nonSriLankan() {
    document.getElementById('div1').style.display = 'none';
    document.getElementById('div2').style.display = 'block';
  }

  loadsinhala() {
    const sinhalOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.SINHALESE],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TrSinhalaControl = new google.elements.transliteration.TransliterationControl(sinhalOptions);
    TrSinhalaControl.makeTransliteratable(this.sinElements);
  }

  loadTamil() {
    const tamilOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.TAMIL],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TrtamilControl = new google.elements.transliteration.TransliterationControl(tamilOptions);
    TrtamilControl.makeTransliteratable(this.tamilelements);
  }

  adloadsinhala() {
    const sinhalOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.SINHALESE],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TrSinhalaControl = new google.elements.transliteration.TransliterationControl(sinhalOptions);
    TrSinhalaControl.makeTransliteratable(this.adsinElements);
  }

  adloadTamil() {
    const tamilOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.TAMIL],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TrtamilControl = new google.elements.transliteration.TransliterationControl(tamilOptions);
    TrtamilControl.makeTransliteratable(this.adtamilelements);
  }

  @HostListener('keydown', ['$event']) onKeyDown(e) {
    if (e.keyCode === 32 || e.keyCode === 13 || e.keyCode === 46 || e.keyCode === 8) {
      setTimeout(() => {
        this.sinFirmName = this.sinElements[0].value;
        this.tamFirmName = this.tamilelements[0].value;
        this.sinFirmAd = this.adsinElements[0].value;
        this.tamFirmAd = this.adtamilelements[0].value;
      },
        1000);
    }
  }


  isAuditorRegSL(nic) {
    if (this.auditorFirmPartnerDetails.length > 0) {
      for (let i in this.auditorFirmPartnerDetails) {
        if (nic.toUpperCase() === this.auditorFirmPartnerDetails[i]['nic']) {
          alert('Do not add same partner again');
          return false;
        }
      }
    }
    const data = {
      nic: nic.toUpperCase(),
      qualification: this.qualification
    };
    this.auditorService.auditorDataSL(data)
      .subscribe(
        req => {
          if (req['isauditor']) {
            if (req['data']['auddata']) {
              this.auditorDetails.id = req['data']['auddata']['id'];
              this.auditorDetails.title = req['data']['auddata']['title'];
              this.auditorDetails.firstname = req['data']['auddata']['first_name'];
              this.auditorDetails.lastname = req['data']['auddata']['last_name'];
              this.auditorDetails.businessName = req['data']['auddata']['business_name'];
              this.auditorDetails.birthDay = req['data']['auddata']['dob'];
              this.auditorDetails.nationality = req['data']['auddata']['nationality'];
              this.auditorDetails.race = req['data']['auddata']['race'];
              this.auditorDetails.isUnsoundMind = req['data']['auddata']['is_unsound_mind'];
              this.auditorDetails.isInsolventOrBankrupt = req['data']['auddata']['is_insolvent_or_bankrupt'];
              this.auditorDetails.reason1 = req['data']['auddata']['reason'];
              this.auditorDetails.isCompetentCourt = req['data']['auddata']['is_competent_court'];
              this.auditorDetails.reason2 = req['data']['auddata']['competent_court_type'];
              this.auditorDetails.otherDetails = req['data']['auddata']['other_details'];
              this.slModal.show();
            }
          }
          else {
            alert('Please note that you have to register as an auditor at the ROC auditor registration division to be eligible for apply as a firm!');
            this.qualification = '';
          }
        },
        error => {
          console.log(error);
        }
      );
  }
  isAuditorRegNonSL(passport) {
    if (this.auditorFirmPartnerDetails.length > 0) {
      for (let i in this.auditorFirmPartnerDetails) {
        if (passport === this.auditorFirmPartnerDetails[i]['passport']) {
          alert('Do not add same partner again');
          return false;
        }
      }
    }
    const data = {
      passport: passport,
    };
    this.auditorService.auditorDataNonSL(data)
      .subscribe(
        req => {
          if (req['isauditor']) {
            if (req['data']['auddata']) {
              this.auditorDetails.id = req['data']['auddata']['id'];
              this.auditorDetails.title = req['data']['auddata']['title'];
              this.auditorDetails.firstname = req['data']['auddata']['first_name'];
              this.auditorDetails.lastname = req['data']['auddata']['last_name'];
              this.auditorDetails.businessName = req['data']['auddata']['business_name'];
              this.auditorDetails.birthDay = req['data']['auddata']['dob'];
              this.auditorDetails.nationality = req['data']['auddata']['nationality'];
              this.auditorDetails.whereDomiciled = req['data']['auddata']['where_domiciled'];
              this.auditorDetails.dateTakeResidenceInSrilanka = req['data']['auddata']['from_residence_in_srilanka'];
              this.auditorDetails.dateConResidenceInSrilanka = req['data']['auddata']['continuously_residence_in_srilanka'];
              this.auditorDetails.ownedProperty = req['data']['auddata']['particulars_of_immovable_property'];
              this.auditorDetails.otherFacts = req['data']['auddata']['other_facts_to_the_srilanka_domicile'];
              this.auditorDetails.race = req['data']['auddata']['race'];
              this.auditorDetails.isUnsoundMind = req['data']['auddata']['is_unsound_mind'];
              this.auditorDetails.isInsolventOrBankrupt = req['data']['auddata']['is_insolvent_or_bankrupt'];
              this.auditorDetails.reason1 = req['data']['auddata']['reason'];
              this.auditorDetails.isCompetentCourt = req['data']['auddata']['is_competent_court'];
              this.auditorDetails.reason2 = req['data']['auddata']['competent_court_type'];
              this.auditorDetails.otherDetails = req['data']['auddata']['other_details'];
              this.nonslModal.show();
            }
          }
          else {
            alert('Please note that you have to register as an auditor at the ROC auditor registration division to be eligible for apply as a firm!');
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  changeProgressStatuses(newStatus = 1) {
    this.stepOn = newStatus;
    this.progress.progressPercentage = (this.stepOn >= 4) ? (12.5 * 2 + this.stepOn * 12.5) + '%' : (12.5 + this.stepOn * 12.5) + '%';
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

  addAuditorFirmPartnerDetailsToArray() {
    const data = {
      nic: this.nic.toUpperCase(),
      passport: this.passport,
      audId: this.auditorDetails['id'],
      fname: this.auditorDetails['firstname'],
      lname: this.auditorDetails['lastname'],
      otherState: this.otherState === undefined ? '---' : this.otherState,
    };
    this.auditorFirmPartnerDetails.push(data);
    this.slModal.hide();
    this.nonslModal.hide();
    this.resetFirmPartnerDetails();
  }
  removeFirmPartnerDetailsFromArray(index) {
    if (index > -1) {
      this.auditorFirmPartnerDetails.splice(index, 1);
    }
  }
  resetFirmPartnerDetails() {
    this.nic = '';
    this.passport = '';
    this.otherState = '';
  }

  loadAuditorFirmData(firmId) {
    const data = {
      firmId: firmId,
    };
    this.auditorService.auditorFirmDataLoad(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.auditorFirmDetails.firmName = req['data']['firm']['name'];
            this.email = req['data']['firm']['email'];
            this.mobile = req['data']['firm']['mobile'];
            this.tel = req['data']['firm']['telephone'];
            this.sinFirmName = req['data']['firm']['name_si'];
            this.tamFirmName = req['data']['firm']['name_ta'];
            this.sinFirmAd = req['data']['firm']['address_si'];
            this.tamFirmAd = req['data']['firm']['address_ta'];
            this.auditorFirmDetails.isExistAud = req['data']['firm']['is_existing_auditor_firm'] + '';
            if (this.auditorFirmDetails.isExistAud === '1') {
              this.auditorFirmDetails.certificateNo = req['data']['certificateNumber'];
              this.show6();
            } else {
              this.auditorFirmDetails.certificateNo = '';
            }
            this.auditorFirmDetails.businessLocalAddress1 = req['data']['firmaddress']['address1'];
            this.auditorFirmDetails.businessLocalAddress2 = req['data']['firmaddress']['address2'];
            this.auditorFirmDetails.businessPostCode = req['data']['firmaddress']['postcode'];

            this.businessProvince = req['data']['firmaddress']['province'];
            this.auditorFirmDetails.businessProvince = this.businessProvince;

            this.businessDistrict = req['data']['firmaddress']['district'];
            this.auditorFirmDetails.businessDistrict = this.businessDistrict;

            this.businessCity = req['data']['firmaddress']['city'];
            this.auditorFirmDetails.businessCity = this.businessCity;

            this.bgnDivision = req['data']['firmaddress']['gn_division'];
            this.auditorFirmDetails.gnDivision = this.bgnDivision;

            for (let i in req['data']['auditors']) {
              const data2 = {
                nic: req['data']['auditors'][i]['nic'],
                passport: req['data']['auditors'][i]['passport'],
                audId: req['data']['auditors'][i]['id'],
                fname: req['data']['auditors'][i]['fname'],
                lname: req['data']['auditors'][i]['lname'],
                otherState: req['data']['auditors'][i]['other_state'],
              };
              this.auditorFirmPartnerDetails.push(data2);
            }

            this.auditorValidationStep1();
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  // for complete registration without payment...
  submit() {
    if (this.auditorFirmDetails.isExistAud === '1') {
      if (confirm('Existing auditor Firm')) {
        const data = {
          audId: this.firmId,
          type: 'isExistingFirm',
        };
        this.auditorService.auditorStatusUpdate(data)
          .subscribe(
            req => {
              if (req['status']) {
                this.router.navigate(['dashboard/selectregisterauditor']);
              } else {
                alert('Error!');
              }
            }
          );
      }
    } else if (this.auditorFirmDetails.isExistAud === '0') {
      this.changeProgressStatuses(3);
    }
  }

  auditorFirmDataSubmit() {
    const data = {
      firmId: this.firmId,
      sinFirmName: this.sinFirmName,
      tamFirmName: this.tamFirmName,
      sinFirmAd: this.sinFirmAd,
      tamFirmAd: this.tamFirmAd,
      email: this.email,
      mobile: this.mobile,
      tel: this.tel,
      isExistAud: this.auditorFirmDetails['isExistAud'],
      certificateNo: this.auditorFirmDetails['certificateNo'],
      loggedInUser: this.loggedinUserEmail,
      firmName: this.auditorFirmDetails['firmName'],
      businessLocalAddress1: this.auditorFirmDetails['businessLocalAddress1'],
      businessLocalAddress2: this.auditorFirmDetails['businessLocalAddress2'],
      businessProvince: this.auditorFirmDetails.businessProvince.description_en === undefined ? this.businessProvince : this.auditorFirmDetails.businessProvince.description_en,
      businessDistrict: this.auditorFirmDetails.businessDistrict.description_en === undefined ? this.businessDistrict : this.auditorFirmDetails.businessDistrict.description_en,
      businessCity: this.auditorFirmDetails.businessCity.description_en === undefined ? this.businessCity : this.auditorFirmDetails.businessCity.description_en,
      gnDivision: this.auditorFirmDetails.gnDivision.description_en === undefined ? this.bgnDivision : this.auditorFirmDetails.gnDivision.description_en,
      businessPostCode: this.auditorFirmDetails['businessPostCode'],
      firmPartner: this.auditorFirmPartnerDetails,
      qualification: this.qualification
    };
    this.auditorService.auditorFirmDataSubmit(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.blockBackToForm = false;
            this.firmId = req['firmId'];
            this.getAuditorID(this.firmId);
            this.changeProgressStatuses(1);
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  getAuditorID(firmId) {
    const data = {
      firmId: firmId,
    };
    this.auditorService.getAuditorID(data)
      .subscribe(
        req => {
          if (req['audidlist']) {
            this.auditorIDs = [];
            for (let i in req['audidlist']) {
              const data1 = {
                id: req['audidlist'][i]['auditor_id'],
              };
              this.auditorIDs.push(data1);
            }
            this.gotoPay();
            console.log(this.auditorIDs.length);
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  clickDownload(audId) {
    this.auditorGeneratePDF(audId, this.firmId);
  }
  auditorGeneratePDF(audId, firmId) {
    this.spinner.show();
    this.auditorService.auditorFirmPDF(audId, firmId)
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

  // for uplaod auditor pdf files...
  fileUpload(event, description, docType) {

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
      formData.append('firmId', this.firmId.toString());
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getAuditorFileUploadUrl();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            const datas = {
              id: data['docid'],
              name: data['name'],
              token: data['token'],
              pdfname: data['pdfname'],
            };
            if (docType === 'applicationUpload') {
              this.application.push(datas);
              this.gotoPay();
            } else if (docType === 'regCertificateUpload') {
              this.regCertificateUploadList.push(datas);
              this.gotoPay();
            }
            this.spinner.hide();
            this.description = '';
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
    };
    this.spinner.show();
    this.auditorService.auditorDeleteUploadedPdf(data)
      .subscribe(
        rq => {
          this.spinner.hide();
          if (index > -1) {
            if (docType === 'applicationUpload') {
              this.application.splice(index, 1);
              this.gotoPay();
            } else if (docType === 'regCertificateUpload') {
              this.regCertificateUploadList.splice(index, 1);
              this.gotoPay();
            }
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
        }
      );
  }
  // for load uplaoded auditor all pdf files...
  loadUploadedFile(firmId) {
    const data = {
      audId: firmId,
      type: 'firm',
    };
    this.auditorService.auditorFiles(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['file']) {
              for (let i in req['data']['file']) {
                const data1 = {
                  id: req['data']['file'][i]['id'],
                  key: req['data']['file'][i]['dockey'],
                  token: req['data']['file'][i]['file_token'],
                  pdfname: req['data']['file'][i]['name'],
                };
                if (req['data']['file'][i]['dockey'] === 'AUDITOR_APPLICATION') {
                  this.application.push(data1);
                  this.gotoPay();
                } else if (req['data']['file'][i]['dockey'] === 'AUDITOR_CERTIFICATE') {
                  this.regCertificateUploadList.push(data1);
                  this.gotoPay();
                }
              }
            }
          }
        }
      );
  }
  // for view the uploaded pdf...
  ngOnDownload(token: string): void {
    this.spinner.show();
    this.general.getDocumenttoServer(token, 'CAT_AUDITOR_DOCUMENT')
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
  // for the payment process...
  auditorPay() {
    const data = {
      audId: this.firmId,
      audType: 'firm',
    };
    this.auditorService.auditorPay(data)
      .subscribe(
        req => {
          if (req['status']) {
            alert('Payment Successful');
            this.router.navigate(['dashboard/selectregisterauditor']);
          }
        },
        error => {
          console.log(error);
        }
      );
  }
  getCipherToken() {
    if (!this.firmId) { return this.router.navigate(['dashboard/selectregisterauditor']); }
    const item: Array<Item> = [{
      fee_type: 'PAYMENT_FIRM_AS_AUDITORS',
      description: 'For register of a Auditor Firm (Register Request)',
      quantity: 1,
    }];
    const buy: IBuy = {
      module_type: 'MODULE_AUDITOR_FIRM',
      module_id: this.firmId.toString(),
      description: 'Auditor Firm Registration',
      item: item,
      extraPay: null
    };
    this.crToken.getCrToken(buy).subscribe(
      req => {
        this.cipher_message = req.token;
        this.blockPayment = true;
      },
      error => { this.snotifyService.error(error, 'error'); }
    );
  }

  auditorValidationStep1() {
    if ((typeof this.auditorFirmPartnerDetails !== 'undefined' && this.auditorFirmPartnerDetails != null && this.auditorFirmPartnerDetails.length != null && this.auditorFirmPartnerDetails.length > 0) &&
      this.auditorFirmDetails.firmName &&
      this.email && this.validateEmail(this.email) &&
      this.mobile && this.phonenumber(this.mobile) &&
      this.phonenumber(this.tel) &&
      this.auditorFirmDetails.isExistAud &&
      this.auditorFirmDetails.businessLocalAddress1 &&
      this.auditorFirmDetails.businessLocalAddress2 &&
      this.auditorFirmDetails.businessProvince &&
      this.auditorFirmDetails.businessDistrict &&
      this.auditorFirmDetails.businessCity &&
      this.auditorFirmDetails.gnDivision &&
      this.auditorFirmDetails.businessPostCode

    ) {
      this.enableStep1Submission = true;
      this.validateExsistingAud();
    } else {
      this.enableStep1Submission = false;
    }
  }

  validateExsistingAud() {
    if (this.auditorFirmDetails.isExistAud === '1') {
      if (this.auditorFirmDetails.certificateNo && this.certnum(this.auditorFirmDetails.certificateNo)) {
        this.enableStep1Submission = true;
      } else {
        this.enableStep1Submission = false;
      }
    } else if (this.auditorFirmDetails.isExistAud === '0') {
      this.enableStep1Submission = true;
    }
  }

  gotoPay() {
    if (this.auditorFirmDetails.isExistAud === '1') {
      if ((typeof this.application !== 'undefined' && this.application != null && this.application.length != null && this.application.length === this.auditorIDs.length) &&
        (typeof this.regCertificateUploadList !== 'undefined' && this.regCertificateUploadList != null && this.regCertificateUploadList.length != null && this.regCertificateUploadList.length > 0)) {
        this.enableGoToPay = true;
      } else {
        this.enableGoToPay = false;
      }
    } else if (this.auditorFirmDetails.isExistAud === '0') {
      if (typeof this.application !== 'undefined' && this.application != null && this.application.length != null && this.application.length === this.auditorIDs.length) {
        this.enableGoToPay = true;
      } else {
        this.enableGoToPay = false;
      }
    }
  }

  // for confirm to going document download step...
  areYouSureYes() {
    this.blockBackToForm = true;
  }
  areYouSureNo() {
    this.blockBackToForm = false;
  }
  // for confirm to complete payment step...
  areYouSurePayYes() {
    this.getCipherToken();
    this.blockPayment = true;
  }
  areYouSurePayNo() {
    this.blockPayment = false;
  }
  nicValidate(nic) {
    if (!nic) {
      return true;
    }
    let regx = /^[0-9]{9}[x|X|v|V]|[0-9]{12}$/;
    if (nic.match(regx)) {
      this.enableNic = true;
    } else {
      this.enableNic = false;
    }
    return nic.match(regx);
  }

  private certnum(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let code = /^.{3,}$/;
    return inputtxt.match(code);
  }

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

  private phonenumber(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let phoneno = /^\d{10}$/;
    return inputtxt.match(phoneno);
  }

}
