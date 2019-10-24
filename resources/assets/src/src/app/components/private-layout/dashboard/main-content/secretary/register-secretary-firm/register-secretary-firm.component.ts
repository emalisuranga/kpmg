import { ToastrService } from 'ngx-toastr';
import { NgxSpinnerService } from 'ngx-spinner';
import { HttpClient } from '@angular/common/http';
import { HttpHeaders } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { DomSanitizer } from '@angular/platform-browser';
import { Component, OnInit, HostListener } from '@angular/core';
import { SecretaryDataService } from '../secretary-data.service';
import { DataService } from '../../../../../../storage/data.service';
import { Item, IBuy } from './../../../../../../http/models/payment';
import { environment } from '../../../../../../../environments/environment';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { SecretaryService } from '../../../../../../http/services/secretary.service';
import { CalculationService } from './../../../../../../http/shared/calculation.service';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { ISecretaryDataFirm, ISecretaryFirmPartnerData } from '../../../../../../http/models/secretary.model';
declare var google: any;

@Component({
  selector: 'app-register-secretary-firm',
  templateUrl: './register-secretary-firm.component.html',
  styleUrls: ['./register-secretary-firm.component.scss']
})
export class RegisterSecretaryFirmComponent implements OnInit {

  firmId: number;
  loggedinUserEmail: string;
  nic: string;
  visiblePartnerDetailBlock = false;

  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;

  // secretary details object to register as a natural person...
  secretaryFirmDetails: ISecretaryDataFirm = {
    loggedInUser: '', sinName: '', tamName: '', id: 0, name: '', registrationNumber: '', businessLocalAddress1: '', businessLocalAddress2: '',
    businessProvince: undefined, isUndertakeSecWork: '', businessDistrict: undefined, businessCity: undefined, bgnDivision: undefined, businessPostCode: '',
    isUnsoundMind: '', isInsolventOrBankrupt: '', reason1: '', isCompetentCourt: '', reason2: '', firmPartners: '', type: '', isExistSec: '', certificateNo: '',
  };
  secretaryFirmPartners: ISecretaryFirmPartnerData = { id: '', name: '', residentialAddress: '', citizenship: '', whichQualified: '', pQualification: '', };

  secretaryFirmPartnerDetails = [];

  enableStep1Submission = false;
  enableStep2Submission = false;
  enablePartnerSubmission = false;
  enableGoToPay = false;
  enableNic = false;

  blockBackToForm = false;
  blockPayment = false;
  cipher_message: string;

  businessProvince: string;
  businessDistrict: string;
  businessCity: string;
  bgnDivision: string;

  sinFirmName = null;
  tamFirmName = null;
  tamilelements;
  sinElements;

  email: any;
  mobile: any;
  tel: any;

  firmType: string;

  processStatus: string;
  application = [];
  regCertificateUploadList = [];
  other = [];
  stepOn = 0;
  progress = {

    stepArr: [
      { label: 'Firm Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Add Partners', icon: 'fa fa-users', status: '' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },
    ],
    progressPercentage: '10%'
  };

  constructor(public data: DataService,
    private secretaryService: SecretaryService,
    private helper: HelperService,
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private SecData: SecretaryDataService,
    private general: GeneralService,
    private crToken: PaymentService,
    private snotifyService: ToastrService,
    public calculation: CalculationService,
  ) {

    this.firmId = this.SecData.getFirmId; // for continue upload process after canceled...
    if (!(this.firmId === undefined)) {
      this.loadSecretaryFirmData(this.firmId);
      // this.loadUploadedFile(this.firmId);
      this.changeProgressStatuses(2);
      this.SecData.firmId = undefined;
    }

    this.loggedinUserEmail = localStorage.getItem('currentUser');
    this.loggedinUserEmail = this.loggedinUserEmail.replace(/^"(.*)"$/, '$1');

  }

  ngOnInit() {
    document.getElementById('div1').style.display = 'none';
    document.getElementById('div2').style.display = 'none';
    document.getElementById('div3').style.display = 'none';
    this.sinElements = document.getElementsByClassName('sinhalaname');
    this.tamilelements = document.getElementsByClassName('tamilname');
  }

  /*.....below show () functions for the radio buttons....*/
  show1() {
    document.getElementById('div1').style.display = 'none';
    this.secretaryFirmDetails['reason1'] = '';
  }
  show2() {
    document.getElementById('div1').style.display = 'block';
  }
  show3() {
    document.getElementById('div2').style.display = 'none';
    this.secretaryFirmDetails['reason2'] = '';
  }
  show4() {
    document.getElementById('div2').style.display = 'block';
  }
  show5() {
    document.getElementById('div3').style.display = 'none';
    this.secretaryFirmDetails['certificateNo'] = '';
  }
  show6() {
    document.getElementById('div3').style.display = 'block';
  }

  sanitize(url: string) {
    return this.sanitizer.bypassSecurityTrustUrl(url);
  }
  changeProgressStatuses(newStatus = 1) {
    this.stepOn = newStatus;
    this.progress.progressPercentage = (this.stepOn >= 4) ? (10 * 2 + this.stepOn * 20) + '%' : (10 + this.stepOn * 20) + '%';
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

  loadSecretaryFirmData(firmId) {
    const data = {
      firmId: firmId,
    };
    this.secretaryService.secretaryFirmDataLoad(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.sinFirmName = req['data']['firmDetails']['name_si'];
            this.tamFirmName = req['data']['firmDetails']['name_ta'];
            this.firmType = req['data']['firmDetails']['firm_type'];
            this.secretaryFirmDetails.name = req['data']['firmDetails']['name'];
            this.email = req['data']['firmDetails']['email'];
            this.mobile = req['data']['firmDetails']['mobile'];
            this.tel = req['data']['firmDetails']['telphone'];
            this.secretaryFirmDetails.registrationNumber = req['data']['firmDetails']['registration_no'];
            this.secretaryFirmDetails.isUndertakeSecWork = req['data']['firmDetails']['is_undertake_secretary_work'];
            this.secretaryFirmDetails.isUnsoundMind = req['data']['firmDetails']['is_unsound_mind'];
            this.secretaryFirmDetails.isInsolventOrBankrupt = req['data']['firmDetails']['is_insolvent_or_bankrupt'];
            this.secretaryFirmDetails.isCompetentCourt = req['data']['firmDetails']['is_competent_court'];
            this.secretaryFirmDetails.isExistSec = req['data']['firmDetails']['is_existing_secretary_firm'] + '';
            if (this.secretaryFirmDetails.isInsolventOrBankrupt === 'yes') {
              this.secretaryFirmDetails.reason1 = req['data']['firmDetails']['reason'];
              this.show2();
            } else {
              this.secretaryFirmDetails.reason1 = '';
            }
            if (this.secretaryFirmDetails.isCompetentCourt === 'yes') {
              if (req['data']['firmDetails']['competent_court_type'] === 'pardoned') {
                this.secretaryFirmDetails.reason2 = 'pardoned';
                this.show4();
              } else if (req['data']['firmDetails']['competent_court_type'] === 'appeal') {
                this.secretaryFirmDetails.reason2 = 'appeal';
                this.show4();
              }
            } else {
              this.secretaryFirmDetails.reason2 = '';
            }
            if (this.secretaryFirmDetails.isExistSec === '1') {
              this.secretaryFirmDetails.certificateNo = req['data']['certificateNumber'];
              this.show6();
            } else {
              this.secretaryFirmDetails.certificateNo = '';
            }
            this.secretaryFirmDetails.businessLocalAddress1 = req['data']['firmAddress']['address1'];
            this.secretaryFirmDetails.businessLocalAddress2 = req['data']['firmAddress']['address2'];
            this.secretaryFirmDetails.businessPostCode = req['data']['firmAddress']['postcode'];

            this.businessProvince = req['data']['firmAddress']['province'];
            this.secretaryFirmDetails.businessProvince = this.businessProvince;

            this.businessDistrict = req['data']['firmAddress']['district'];
            this.secretaryFirmDetails.businessDistrict = this.businessDistrict;

            this.businessCity = req['data']['firmAddress']['city'];
            this.secretaryFirmDetails.businessCity = this.businessCity;

            this.bgnDivision = req['data']['firmAddress']['gn_division'];
            this.secretaryFirmDetails.bgnDivision = this.bgnDivision;

            for (let i in req['data']['partnerDetails']) {
              const data1 = {
                id: req['data']['partnerDetails'][i]['nic'],
                name: req['data']['partnerDetails'][i]['name'],
                residentialAddress: req['data']['partnerDetails'][i]['address'],
                citizenship: req['data']['partnerDetails'][i]['citizenship'],
                whichQualified: req['data']['partnerDetails'][i]['which_qualified'],
                pQualification: req['data']['partnerDetails'][i]['professional_qualifications'],
              };
              this.secretaryFirmPartnerDetails.push(data1);
            }
            this.secretaryFirmValidationStep1();
            this.secretaryFirmValidationStep2();
            this.loadUploadedFile(this.firmId);
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  secretaryFirmDataSubmit() {

    if (this.secretaryFirmDetails['isCompetentCourt'] === 'no') {
      this.secretaryFirmDetails['reason2'] = 'no';
    }
    if (this.secretaryFirmDetails['isInsolventOrBankrupt'] === 'no') {
      this.secretaryFirmDetails['reason1'] = '';
    }
    const data = {
      loggedInUser: this.loggedinUserEmail,
      sinName: this.sinFirmName,
      tamName: this.tamFirmName,
      firmType: this.firmType,
      email: this.email,
      mobile: this.mobile,
      tel: this.tel,
      isExistSec: this.secretaryFirmDetails['isExistSec'],
      certificateNo: this.secretaryFirmDetails['certificateNo'],
      id: this.secretaryFirmDetails['id'],
      name: this.secretaryFirmDetails['name'],
      registrationNumber: this.secretaryFirmDetails['registrationNumber'],
      businessLocalAddress1: this.secretaryFirmDetails['businessLocalAddress1'],
      businessLocalAddress2: this.secretaryFirmDetails['businessLocalAddress2'],
      businessProvince: this.secretaryFirmDetails.businessProvince.description_en === undefined ? this.businessProvince : this.secretaryFirmDetails.businessProvince.description_en,
      businessDistrict: this.secretaryFirmDetails.businessDistrict.description_en === undefined ? this.businessDistrict : this.secretaryFirmDetails.businessDistrict.description_en,
      businessCity: this.secretaryFirmDetails.businessCity.description_en === undefined ? this.businessCity : this.secretaryFirmDetails.businessCity.description_en,
      bgnDivision: this.secretaryFirmDetails.bgnDivision.description_en === undefined ? this.bgnDivision : this.secretaryFirmDetails.bgnDivision.description_en,
      businessPostCode: this.secretaryFirmDetails['businessPostCode'],
      isUndertakeSecWork: this.secretaryFirmDetails['isUndertakeSecWork'],
      isUnsoundMind: this.secretaryFirmDetails['isUnsoundMind'],
      isInsolventOrBankrupt: this.secretaryFirmDetails['isInsolventOrBankrupt'],
      reason1: this.secretaryFirmDetails['reason1'],
      isCompetentCourt: this.secretaryFirmDetails['isCompetentCourt'],
      reason2: this.secretaryFirmDetails['reason2'],
      firmPartners: this.secretaryFirmPartnerDetails,
      type: 'firm',
    };
    this.secretaryService.secretaryFirmDataSubmit(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.firmId = req['firmId'];
            this.blockBackToForm = false;
            this.changeProgressStatuses(2);
          } else {
            alert('Firm already registered for this Registration No');
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  // for complete registration without payment...
  submit() {
    if (this.secretaryFirmDetails.isExistSec === '1') {
      if (confirm('Are you sure you want to submit as a existing secretary firm')) {
        const data = {
          secId: this.firmId,
          type: 'isExistingFirm',
        };
        this.secretaryService.secretaryStatusUpdate(data)
          .subscribe(
            req => {
              if (req['status']) {
                this.router.navigate(['dashboard/selectregistersecretary']);
              } else {
                alert('Error!');
              }
            }
          );
      }
    } else if (this.secretaryFirmDetails.isExistSec === '0') {
      this.changeProgressStatuses(4);
    }
  }


  addSecretaryFirmPartnerDetailsToArray() {
    const data = {
      id: this.secretaryFirmPartners['id'],
      name: this.secretaryFirmPartners['name'],
      residentialAddress: this.secretaryFirmPartners['residentialAddress'],
      citizenship: this.secretaryFirmPartners['citizenship'],
      whichQualified: this.secretaryFirmPartners['whichQualified'],
      pQualification: this.secretaryFirmPartners['pQualification']
    };
    this.secretaryFirmPartnerDetails.push(data);
    this.resetFirmPartnerDetails();
    this.visiblePartnerDetailBlock = false;
  }
  removeFirmPartnerDetailsFromArray(index) {
    if (index > -1) {
      this.secretaryFirmPartnerDetails.splice(index, 1);
    }
  }
  resetFirmPartnerDetails() {
    this.secretaryFirmPartners['name'] = '';
    this.secretaryFirmPartners['residentialAddress'] = '';
    this.secretaryFirmPartners['citizenship'] = '';
    this.secretaryFirmPartners['whichQualified'] = '';
    this.secretaryFirmPartners['pQualification'] = '';
    this.visiblePartnerDetailBlock = false;
    this.enablePartnerSubmission = false;
  }
  clickNic() {
    this.loadSecretaryFirmPartnerData(this.nic.toUpperCase());
    this.nic = '';
    this.enableNic = false;
  }

  loadSecretaryFirmPartnerData(nic) {
    if (this.secretaryFirmPartnerDetails.length > 0) {
      for (let i in this.secretaryFirmPartnerDetails) {
        if (nic === this.secretaryFirmPartnerDetails[i]['id']) {
          alert('Do not add same partner again');
          return false;
        }
      }
    }

    const data = {
      nic: nic,
    };
    this.secretaryService.secretaryFirmPartnerData(data)
      .subscribe(
        req => {
          if (req['status']) {
            const fname = req['data']['partner']['first_name'];
            const lname = req['data']['partner']['last_name'];
            const address1 = req['data']['partnerAddress']['address1'];
            const address2 = req['data']['partnerAddress']['address2'];
            const city = req['data']['partnerAddress']['city'];
            this.secretaryFirmPartners.id = nic;
            this.secretaryFirmPartners.name = (fname + ' ' + lname);
            this.secretaryFirmPartners.residentialAddress = (address1 + ', ' + address2 + ', ' + city);
            this.secretaryFirmPartners.citizenship = 'Srilankan';
            this.secretaryFirmPartners.pQualification = req['data']['partner']['professional_qualifications'];
            this.visiblePartnerDetailBlock = true;
          } else {
            this.visiblePartnerDetailBlock = false;
            alert('This NIC is not registered as a secretary');
          }
        }
      );
  }

  // for download the generated pdf...
  clickDownload() {
    this.secretaryGeneratePDF(this.firmId);
  }
  secretaryGeneratePDF(firmId) {
    this.spinner.show();
    this.secretaryService.secretaryFirmPDF(firmId)
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

  // for uplaod secretary firm application pdf file...
  fileUpload(event, docType) {

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
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getSecretaryFirmFileUploadUrl();
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
            } else if (docType === 'regCertificateUpload') {
              this.regCertificateUploadList.push(datas);
            } else if (docType === 'otherUpload') {
              this.other.push(datas);
            }
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

  // for load uplaoded secretary firm application pdf file...
  loadUploadedFile(firmId) {
    const data = {
      secId: firmId,
      type: 'firm',
    };
    this.secretaryService.secretaryFiles(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['file']) {
              for (let i in req['data']['file']) {
                const data1 = {
                  id: req['data']['file'][i]['id'],
                  name: '',
                  token: req['data']['file'][i]['file_token'],
                  pdfname: req['data']['file'][i]['name'],
                };
                if (req['data']['file'][i]['dockey'] === 'SECRETARY_APPLICATION') {
                  this.application.push(data1);
                  this.gotoPay();
                } else if (req['data']['file'][i]['dockey'] === 'SECRETARY_CERTIFICATE') {
                  this.regCertificateUploadList.push(data1);
                  this.gotoPay();
                } else if (req['data']['file'][i]['dockey'] === 'EXTRA_DOCUMENT') {
                  this.other.push(data1);
                  this.gotoPay();
                }
              }
            }
          }
        }
      );
  }

  // for delete the uploaded pdf from the database...
  fileDelete(docId, docType, index) {
    const data = {
      documentId: docId,
    };
    this.spinner.show();
    this.secretaryService.secretaryDeleteUploadedPdf(data)
      .subscribe(
        rq => {
          this.spinner.hide();
          if (index > -1) {
            if (docType === 'applicationUpload') {
              this.application.splice(index, 1);
            } else if (docType === 'regCertificateUpload') {
              this.regCertificateUploadList.splice(index, 1);
            } else if (docType === 'otherUpload') {
              this.other.splice(index, 1);
            }
            this.gotoPay();
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
    this.general.getDocumenttoServer(token, 'CAT_SECRETARY_DOCUMENT')
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
  secretaryPay() {
    const data = {
      secId: this.firmId,
      secType: 'firm',
    };
    this.secretaryService.secretaryPay(data)
      .subscribe(
        req => {
          if (req['status']) {
            alert('Payment Successful');
            this.router.navigate(['dashboard/selectregistersecretary']);
          }
        },
        error => {
          console.log(error);
        }
      );
  }
  getCipherToken() {
    const item: Array<Item> = [{
      fee_type: 'PAYMENT_FIRM_AS_SECRETARIES',
      description: 'For register of a Secretary (Register Request)',
      quantity: 1,
    }];
    const buy: IBuy = {
      module_type: 'MODULE_SECRETARY_FIRM',
      module_id: this.firmId.toString(),
      description: 'Secretary Registration Firm',
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

  isUndertakeSecWorkPopUp() {
    if (this.secretaryFirmDetails.isUndertakeSecWork === 'no') {
      alert('Warning! You cannot proceed further');
    }
  }
  isUnsoundMindPopUp() {
    if (this.secretaryFirmDetails.isUnsoundMind === 'yes') {
      alert('Warning! You cannot proceed further');
    }
  }


  secretaryFirmValidationStep1() {
    if (
      this.firmType &&
      this.secretaryFirmDetails.name &&
      this.email && this.validateEmail(this.email) &&
      this.mobile && this.phonenumber(this.mobile) &&
      this.phonenumber(this.tel) &&
      this.secretaryFirmDetails.registrationNumber &&
      this.secretaryFirmDetails.businessLocalAddress1 &&
      this.secretaryFirmDetails.businessLocalAddress2 &&
      this.secretaryFirmDetails.businessProvince &&
      this.secretaryFirmDetails.businessCity &&
      this.secretaryFirmDetails.businessDistrict &&
      this.secretaryFirmDetails.bgnDivision &&
      this.secretaryFirmDetails.businessPostCode &&
      this.secretaryFirmDetails.isUndertakeSecWork === 'yes'
    ) {
      this.enableStep1Submission = true;
    } else {
      this.enableStep1Submission = false;
    }
  }
  secretaryFirmValidationStep2() {
    if (this.secretaryFirmDetails.isExistSec &&
      (this.secretaryFirmDetails.isUnsoundMind === 'no') &&
      (this.secretaryFirmDetails.isInsolventOrBankrupt === 'yes' || this.secretaryFirmDetails.isInsolventOrBankrupt === 'no') &&
      (this.secretaryFirmDetails.isCompetentCourt === 'yes' || this.secretaryFirmDetails.isCompetentCourt === 'no') &&
      (typeof this.secretaryFirmPartnerDetails !== 'undefined' && this.secretaryFirmPartnerDetails != null && this.secretaryFirmPartnerDetails.length != null && this.secretaryFirmPartnerDetails.length > 0) // for check aray is not null
    ) {
      this.enableStep2Submission = true;
      if (this.secretaryFirmDetails.isInsolventOrBankrupt === 'yes') {
        if (this.secretaryFirmDetails.reason1) {
          this.enableStep2Submission = true;
          if (this.secretaryFirmDetails.isCompetentCourt === 'yes') {
            if (this.secretaryFirmDetails.reason2 === 'pardoned' || this.secretaryFirmDetails.reason2 === 'appeal') {
              this.enableStep2Submission = true;
              this.validateExsistingSec();
            } else {
              this.enableStep2Submission = false;
            }
          } else if (this.secretaryFirmDetails.isCompetentCourt === 'no') {
            if (this.secretaryFirmDetails.reason2 === 'pardoned' || this.secretaryFirmDetails.reason2 === 'appeal') {
              this.enableStep2Submission = false;
            } else {
              this.enableStep2Submission = true;
              this.validateExsistingSec();
            }
          }
        } else {
          this.enableStep2Submission = false;
        }

      } else if (this.secretaryFirmDetails.isInsolventOrBankrupt === 'no') {
        if (this.secretaryFirmDetails.reason1) {
          this.enableStep2Submission = false;
        } else {
          this.enableStep2Submission = true;

          if (this.secretaryFirmDetails.isCompetentCourt === 'yes') {
            if (this.secretaryFirmDetails.reason2 === 'pardoned' || this.secretaryFirmDetails.reason2 === 'appeal') {
              this.enableStep2Submission = true;
              this.validateExsistingSec();
            } else {
              this.enableStep2Submission = false;
            }
          } else if (this.secretaryFirmDetails.isCompetentCourt === 'no') {
            if (this.secretaryFirmDetails.reason2 === 'pardoned' || this.secretaryFirmDetails.reason2 === 'appeal') {
              this.enableStep2Submission = false;
            } else {
              this.enableStep2Submission = true;
              this.validateExsistingSec();
            }
          }
        }
      }
    } else {
      this.enableStep2Submission = false;
    }
  }

  validateExsistingSec() {
    if (this.secretaryFirmDetails.isExistSec === '1') {
      if (this.secretaryFirmDetails.certificateNo && this.certnum(this.secretaryFirmDetails.certificateNo)) {
        this.enableStep2Submission = true;
      } else {
        this.enableStep2Submission = false;
      }
    } else if (this.secretaryFirmDetails.isExistSec === '0') {
      this.enableStep2Submission = true;
    }
  }

  // validate add partners modal...
  secretaryFirmValidationStep3() {
    if (this.secretaryFirmPartners.name &&
      this.secretaryFirmPartners.residentialAddress &&
      this.secretaryFirmPartners.citizenship &&
      this.secretaryFirmPartners.whichQualified &&
      this.secretaryFirmPartners.pQualification
    ) {
      this.enablePartnerSubmission = true;
    } else {
      this.enablePartnerSubmission = false;
    }
  }
  gotoPay() {
    if (this.secretaryFirmDetails.isExistSec === '1') {
      if ((typeof this.application !== 'undefined' && this.application != null && this.application.length != null && this.application.length > 0) &&
        (typeof this.regCertificateUploadList !== 'undefined' && this.regCertificateUploadList != null && this.regCertificateUploadList.length != null && this.regCertificateUploadList.length > 0)) {
        this.enableGoToPay = true;
      } else {
        this.enableGoToPay = false;
      }
    } else if (this.secretaryFirmDetails.isExistSec === '0') {
      if (typeof this.application !== 'undefined' && this.application != null && this.application.length != null && this.application.length > 0) {
        this.enableGoToPay = true;
      } else {
        this.enableGoToPay = false;
      }
    }
  }
  // for confirm to going document download step...
  areYouSureYes() {
    if (this.secretaryFirmDetails.reason2 === 'appeal') {
      if (confirm('You cannot proceed further because of appeal')) {
        this.router.navigate(['dashboard/selectregistersecretary']);
      }
    } else {
      this.blockBackToForm = true;
    }
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

  @HostListener('keydown', ['$event']) onKeyDown(e) {
    if (e.keyCode === 32 || e.keyCode === 13 || e.keyCode === 46 || e.keyCode === 8) {
      setTimeout(() => {
        this.sinFirmName = this.sinElements[0].value;
        this.tamFirmName = this.tamilelements[0].value;
      },
        1000);
    }
  }

  private certnum(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let code = /^.{6,}$/;
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
