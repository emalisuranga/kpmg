import { Component, OnInit, AfterViewInit, ElementRef } from '@angular/core';
import { DataService } from '../../../../../../storage/data.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { IDebentureRow } from '../../../../../../http/models/debentures.model';
import { AddressChangeService } from '../../../../../../http/services/address-change.service';
import { IssueOfDebenturesService } from '../../../../../../http/services/issue-of-debentures.service';
import { DebenturesDataService } from '../debentures-data.service';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { NgxSpinnerService } from 'ngx-spinner';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { Item, IBuy } from '../../../../../../http/models/payment';
import { Router, ActivatedRoute } from '@angular/router';
import { CalculationService } from './../../../../../../http/shared/calculation.service';
import { environment } from '../../../../../../../environments/environment';
import { ISignedStakeholder } from '../../issue-of-shares-new/models/form6.model';

@Component({
  selector: 'app-issue-of-debentures',
  templateUrl: './issue-of-debentures.component.html',
  styleUrls: ['./issue-of-debentures.component.scss']
})
export class IssueOfDebenturesComponent implements OnInit {

  formattedTodayValue = '';

  progress = {

    stepArr: [
      { label: 'Add new Debenture', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },
    ],

    progressPercentage: '12.5%'

  };

  companyId: number;
  reqId: number;
  preReqId: number;
  status: string;
  type: string;
  companyName: string;
  companyRegno: string;
  description: string;
  dateofissue: any;
  cipher_message: string;
  date = new Date();
  stepOn = 0;
  validationMessage = '';
  validationMessageSubmit = '';
  email = ' ';
  validationMessageType = false;
  validDeb = false;
  validDebEdit = false;
  enableStep1Submission = true;
  hideAndshow = false;
  blockBackToForm = false;
  blockPayment = false;
  validNewDebenture = false;
  addNewDebenture = false;
  routetoDashboard: boolean;
  storage1: any;
  document_confirm = false;
  storage2: any;
  application = [];
  aditionalDocumentList = [];
  preApprovedDeb = [];
  newDebentures = [];
  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;
  newDebenture: IDebentureRow = { id: 0, showEditPaneForMemb: false, totalamountsecured: null, series: null, amount: null, description: null, nameoftrustees: null, dateofcoveringdead: null, dateofresolution: null, dateofissue: null };

  signing_party_designation = '';
  singning_party_name = '';

  signedDirectors: Array<ISignedStakeholder> = [];
  signedSecs: Array<ISignedStakeholder> = [];
  signedSecFirms: Array<ISignedStakeholder> = [];

  // tslint:disable-next-line:no-shadowed-variable
  constructor(private router: Router, public calculation: CalculationService, private crToken: PaymentService, private helper: HelperService, private spinner: NgxSpinnerService, private httpClient: HttpClient, public data: DataService, private general: GeneralService, private addressChangeService: AddressChangeService, private IssueOfDebenturesService: IssueOfDebenturesService, private DebData: DebenturesDataService) {

    this.status = this.DebData.getStatus;
    if (this.status === 'new') {
      this.companyId = this.DebData.getComId;
      this.loadProcesingList();
    } else if (this.status === 'newbuthaspreviousrecord') {
      this.companyId = this.DebData.getComId;
      this.preReqId = this.DebData.getReqId;
      this.loadProcesingList();
      // this.loadPreApproved();
    } else if (this.status === 'processing') {
      this.companyId = this.DebData.getComId;
      this.reqId = this.DebData.getReqId;
      this.loadProcesingList();
      this.loadUploadedFile();
      // this.loadPreApproved();
    }
  }

  ngOnInit() {
    if (!this.DebData.getNavigatetoDashboard){
      this.router.navigate(['dashboard/home']);
    }
    this.formattedTodayValue = this.getFormatedToday();
  }

  changeProgressStatuses(newStatus = 1) {
    this.formattedTodayValue = this.getFormatedToday();
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
    return this.progress;
  }

  getEmail() {

    this.email = localStorage.getItem('currentUser');
    this.email = this.email.replace(/^"(.*)"$/, '$1');
    return this.email;
  }

  // Load previous approved debentures record on bganing...
  loadPreApproved() {

    this.formattedTodayValue = this.getFormatedToday();

    const data = {
      id: this.companyId,
      type: 'submit',
    };
    this.IssueOfDebenturesService.loadPreApproved(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['debentures']) {
              this.preApprovedDeb = [];
              for (let i in req['debentures']) {
                const data1 = {
                  totalAmountSecured: req['debentures'][i]['totalAmountSecured'],
                  series: req['debentures'][i]['series'],
                  amount: req['debentures'][i]['amount'],
                  description: req['debentures'][i]['description'],
                  nameOfTrustees: req['debentures'][i]['nameOfTrustees'],
                  dateOfCoveringDead: req['debentures'][i]['dateOfCoveringDead'],
                  dateOfResolution: req['debentures'][i]['dateOfResolution'],
                  dateOfIssue: req['debentures'][i]['dateOfIssue'],
                };
                this.preApprovedDeb.push(data1);
              }

             /* if (req['processingdeb']) {
                for (let i in req['processingdeb']) {
                  const data1 = {
                    totalAmountSecured: req['processingdeb'][i]['totalAmountSecured'],
                    series: req['processingdeb'][i]['series'],
                    amount: req['processingdeb'][i]['amount'],
                    description: req['processingdeb'][i]['description'],
                    nameOfTrustees: req['processingdeb'][i]['nameOfTrustees'],
                    dateOfCoveringDead: req['processingdeb'][i]['dateOfCoveringDead'],
                    dateOfResolution: req['processingdeb'][i]['dateOfResolution'],
                    dateOfIssue: req['processingdeb'][i]['dateOfIssue'],
                  };
                  this.preApprovedDeb.push(data1);
                }
              }*/

            }
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  // Add new debenture record to processing list...
  addNewCompanyDebenture() {

    const data = {
      showEditPaneForMemb: false,
      totalamountsecured: this.newDebenture['totalamountsecured'],
      series: this.newDebenture['series'],
      amount: this.newDebenture['amount'],
      description: this.newDebenture['description'],
      nameoftrustees: this.newDebenture['nameoftrustees'],
      dateofcoveringdead: this.newDebenture['dateofcoveringdead'],
      dateofresolution: this.newDebenture['dateofresolution'],
      dateofissue: this.newDebenture['dateofissue']
    };
    this.newDebentures.push(data);
    this.newDebenture = { id: 0, showEditPaneForMemb: false, totalamountsecured: null, series: null, amount: null, description: null, nameoftrustees: null, dateofcoveringdead: null, dateofresolution: null, dateofissue: null };
    this.validationMessageSubmit = '';
  }

  // Delete debenture record from list...
  removeFromDebentureList(i) {
    this.newDebentures.splice(i, 1);
  }

  // Validate 'ADD NEW' debenture record...
  validateDebenture() {

    if (!
      (
        this.newDebenture.totalamountsecured && parseFloat(this.newDebenture.totalamountsecured.toString()) &&
        // this.newDebenture.series &&
        this.onlynumbersandchar(this.newDebenture.series) &&
        this.newDebenture.amount && parseFloat(this.newDebenture.amount.toString()) &&
        this.newDebenture.description && this.onlynumbersandchar(this.newDebenture.description) &&
        // this.newDebenture.nameoftrustees && this.onlychars(this.newDebenture.nameoftrustees) &&
       // this.newDebenture.dateofcoveringdead &&
        // this.newDebenture.dateofresolution &&
        this.newDebenture.dateofissue
      )
    ) {
      this.validationMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validDeb = false;
      return false;

    } else {
      this.validationMessage = '';
      this.validDeb = true;
      return true;
    }
  }

  resetSignParty() {
    this.singning_party_name = '';
  }

  // Load procesing debentures list on beganing...
  loadProcesingList() {

    this.formattedTodayValue = this.getFormatedToday();
    this.spinner.show();

    const data = {
      id: this.companyId,
      type: 'submit',
      status: this.DebData.getStatus,
    };
    this.IssueOfDebenturesService.loadProcesingList(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['processingdeb']) {
              this.newDebentures = [];
              for (let i in req['processingdeb']) {

                const data1 = {
                  showEditPaneForMemb: false,
                  id: (undefined !== req['processingdeb'][i]['totalAmountSecured'] && req['processingdeb'][i]['totalAmountSecured'] ) ? req['processingdeb'][i]['totalAmountSecured'] : null,
                  totalamountsecured: req['processingdeb'][i]['totalAmountSecured'],
                  series: req['processingdeb'][i]['series'],
                  amount: req['processingdeb'][i]['amount'],
                  description: req['processingdeb'][i]['description'],
                  nameoftrustees: req['processingdeb'][i]['nameOfTrustees'],
                  dateofcoveringdead: req['processingdeb'][i]['dateOfCoveringDead'],
                  dateofresolution: req['processingdeb'][i]['dateOfResolution'],
                  dateofissue: req['processingdeb'][i]['dateOfIssue'],
                };
                this.newDebentures.push(data1);
              }
            }

            this.signedDirectors = req['directors'];
            this.signedSecs = req['secs'];
            this.signedSecFirms = req['sec_firms'];
            this.singning_party_name = req['signed_party_id'];
            this.signing_party_designation = req['signing_party_designation'];
          }
          this.spinner.hide();
        },
        error => {
          console.log(error);
          this.spinner.hide();
        }
      );
  }

  // Hide and show list
  showToggle(index = 0) {
    this.newDebentures[index]['showEditPaneForMemb'] = !this.newDebentures[index]['showEditPaneForMemb'];
    this.hideAndshow = !this.hideAndshow;
    return true;
  }

  // Edit data in debenture list...
  editMembDataArray(i) {

    const data = {
      showEditPaneForMemb: false,
      totalamountsecured: this.newDebentures[i].totalamountsecured,
      series: this.newDebentures[i].series,
      amount: this.newDebentures[i].amount,
      description: this.newDebentures[i].description,
      nameoftrustees: this.newDebentures[i].nameoftrustees,
      dateofcoveringdead: this.newDebentures[i].dateofcoveringdead,
      dateofresolution: this.newDebentures[i].dateofresolution,
      dateofissue: this.newDebentures[i].dateofissue
    };
    this.newDebentures.splice(i, 1, data);
    this.validationMessageSubmit = '';
    this.hideAndshow = false;
  }

  // Validate debenture record while in list(Toggle status)...
  validateDebentureEdit(i) {

    if (!
      (
        this.newDebentures[i].totalamountsecured && parseFloat(this.newDebentures[i].totalamountsecured.toString()) &&
        // this.newDebentures[i].series &&
        this.onlynumbersandchar(this.newDebentures[i].series) &&
        this.newDebentures[i].amount && parseFloat(this.newDebentures[i].amount.toString()) &&
        this.newDebentures[i].description && this.onlynumbersandchar(this.newDebentures[i].description) &&
        // this.newDebentures[i].nameoftrustees && this.onlychars(this.newDebentures[i].nameoftrustees) &&
      //  this.newDebentures[i].dateofcoveringdead &&
       // this.newDebentures[i].dateofresolution &&
        this.newDebentures[i].dateofissue
      )
    ) {
      this.validationMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validDebEdit = false;
      return false;

    } else {
      this.validationMessage = '';
      this.validationMessageSubmit = '';
      this.validDebEdit = true;
      return true;
    }
  }

  // Validate full list when submit...
  validateDebentureList() {

    this.enableStep1Submission = true;
    if ((typeof this.newDebentures === 'undefined' || this.newDebentures == null || this.newDebentures.length == null || this.newDebentures.length === 0)) {
      this.validationMessageType = true;
      this.enableStep1Submission = false;
    } else {
      for (let i in this.newDebentures) {
        if (!(
          this.newDebentures[i].totalamountsecured && Number(this.newDebentures[i].totalamountsecured) &&
          // this.newDebentures[i].series &&
          this.onlynumbersandchar(this.newDebentures[i].series) &&
          this.newDebentures[i].amount && Number(this.newDebentures[i].amount) &&
          // this.newDebentures[i].nameoftrustees && this.onlychars(this.newDebentures[i].nameoftrustees) &&
          this.newDebentures[i].description && this.onlynumbersandchar(this.newDebentures[i].description) &&
         // this.newDebentures[i].dateofcoveringdead &&
         // this.newDebentures[i].dateofresolution &&
          this.newDebentures[i].dateofissue
        )) {
          this.enableStep1Submission = false;
          this.validationMessageType = false;
        }
      }
    }
    this.stepOneSubmission();
  }

  // Check status before submit...
  stepOneSubmission() {
    if (this.enableStep1Submission) {
      this.debenturesDetailsSubmit();
    } else {
      if (this.validationMessageType) {
        this.blockBackToForm = false;
        this.validationMessageSubmit = 'please add at least one debenture record before continue...';
      } else {
        this.blockBackToForm = false;
        this.validationMessageSubmit = 'Please fill correctly all  required fields denoted by asterik(*)';
      }
    }
  }

  // Debentures details submit...
  debenturesDetailsSubmit() {
    if ((this.status === 'new') || (this.status === 'newbuthaspreviousrecord')) {

      const data = {
        comId: this.companyId,
        email: this.getEmail(),
        debenture_array: this.newDebentures,
        signing_party_designation : this.signing_party_designation,
        signed_party_id: this.singning_party_name,
        type: 'new',
      };
      this.IssueOfDebenturesService.debenturesSubmit(data)
        .subscribe(
          req => {
            if (req['status']) {
              this.blockBackToForm = false;
              this.changeProgressStatuses(1);
              this.reqId = req['companychangerequestId'];
              this.DebData.setStatus('processing');
              this.status = this.DebData.getStatus;
            }
          },
          error => {
            console.log(error);
          }
        );
    } else if (this.status === 'processing') {
      const data = {
        comId: this.companyId,
        email: this.email,
        debenture_array: this.newDebentures,
        type: 'processing',
        reqId: this.reqId,
        signing_party_designation : this.signing_party_designation,
        signed_party_id: this.singning_party_name,
      };

      this.IssueOfDebenturesService.debenturesSubmit(data)
        .subscribe(
          req => {
            if (req['status']) {
              this.blockBackToForm = false;
              this.changeProgressStatuses(1);
            }
          },
          error => {
            console.log(error);
          }
        );
    }
  }

  private onlynumbersandchar(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let numchar = /^[0-9a-zA-Z\s]*$/;
    return inputtxt.match(numchar);
  }

  private onlynumbers(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let num = /^[0-9\s]*$/;
    return inputtxt.match(num);
  }

  private onlychars(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let char = /^[a-zA-Z\s]*$/;
    return inputtxt.match(char);
  }

  // issue of debentures Form 10A download
  issueofDebenturesFormGeneratePDF() {

    const data = {
      comId: this.companyId,
      email: this.getEmail(),
    };
    this.IssueOfDebenturesService.getIssueofDebenturesFormPDFService(data)
      .subscribe(
        response => {
          this.helper.download(response);
        },
        error => {
          console.log(error);
        }
      );
  }

  // for uplaod issue of debentures pdf files...
  fileUpload(event, description, docType) {

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
      formData.append('comId', this.companyId.toString());
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getIssueofDebentureFileUploadUrl();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            const datas = {
              id: data['docid'],
              name: data['name'],
              token: data['token'],
              pdfname: data['pdfname'],
              file_description: data['file_description'],
              docType: data['doctype'],
            };
            if (docType === 'applicationUpload') {
              this.application.push(datas);
            } else if (docType === 'aditionalDocumentsUpload') {
              this.aditionalDocumentList.push(datas);
            }
            this.description = '';
            this.spinner.hide();
          },
          error => {
            this.spinner.hide();
          }
        );
    }
  }

  // For loading uploaded file...
  loadUploadedFile() {

    const data = {
      comId: this.companyId,
      type: 'submit'
    };
    this.IssueOfDebenturesService.issueofdebenturesFiles(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['file']) {
              this.application = [];
              this.aditionalDocumentList = [];
              for (let i in req['data']['file']) {
                const data1 = {
                  id: req['data']['file'][i]['id'],
                  name: req['data']['file'][i]['docname'],
                  key: req['data']['file'][i]['docKey'],
                  token: req['data']['file'][i]['file_token'],
                  pdfname: req['data']['file'][i]['name'],
                  description: req['data']['file'][i]['description'],
                  file_description: req['data']['file'][i]['file_description'],
                };
                if (req['data']['file'][i]['docKey'] === 'ISSUE_OF_DEBENTURES_FORM10A') {
                  this.application.push(data1);

                } else if (req['data']['file'][i]['docKey'] === 'ISSUE_OF_DEBENTURES_ADDITIONAL_DOCUMENT') {
                  this.aditionalDocumentList.push(data1);
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

  // for delete the uploaded pdf from the database...
  fileDelete(docId, docType, index) {

    const data = {
      documentId: docId,
    };
    this.spinner.show();
    this.IssueOfDebenturesService.issueofdebenturesDeleteUploadedPdf(data)
      .subscribe(
        response => {
          if (response['status']) {

            this.loadUploadedFile();
            this.spinner.hide();
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
        }
      );
  }

  getCipherToken() {
    if (!this.companyId) { return this.router.navigate(['dashboard/home']); }

    const item: Array<Item> = [{
      fee_type: 'PAYMENT_COMPANY_ISSUE_OF_DEBENTURES',
      description: 'For Company Issue of Debentures (Change Request)',
      quantity: 1,
    }];

    const buy: IBuy = {
      module_type: 'MODULE_COMPANY_ISSUE_OF_DEBENTURES',
      module_id: this.reqId.toString(),
      description: 'Company Issue Of Debentures',
      item: item,
      extraPay: null
    };

    this.crToken.getCrToken(buy).subscribe(
      req => {
        this.cipher_message = req.token;
        this.blockPayment = true;

      },
      error => { console.log(error); }
    );
  }

  areYouSurePayYes() {
    this.blockPayment = true;
  }
  areYouSurePayNo() {
    this.blockPayment = false;
  }

  areYouSureYes() {
    this.blockBackToForm = true;
  }
  areYouSureNo() {
    this.blockBackToForm = false;
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
