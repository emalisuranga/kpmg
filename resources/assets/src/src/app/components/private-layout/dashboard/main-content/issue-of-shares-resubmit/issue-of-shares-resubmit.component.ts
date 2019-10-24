import { Component, OnInit, AfterViewInit, ElementRef } from '@angular/core';
import { DataService } from '../../../../../storage/data.service';
import { GeneralService } from '../../../../../http/services/general.service';
import { ISharesData, ICsvSupport, ICountry } from '../../../../../http/models/shares.model';
import { IssueOfSharesService } from '../../../../../http/services/issue-of-shares.service';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { NgxSpinnerService } from 'ngx-spinner';
import { APIConnection } from '../../../../../http/services/connections/APIConnection';
import { HelperService } from '../../../../../http/shared/helper.service';
import { Router, ActivatedRoute } from '@angular/router';
import { CalculationService } from './../../../../../http/shared/calculation.service';
import { AppLoadService } from 'src/app/http/shared/app-load.service';
import { ViewChild } from '@angular/core';



@Component({
  selector: 'app-issue-of-shares-resubmit',
  templateUrl: './issue-of-shares-resubmit.component.html',
  styleUrls: ['./issue-of-shares-resubmit.component.scss']
})
export class IssueOfSharesResubmitComponent implements OnInit {
  companyId: string;
  sharesDetailsId: string;
  changeReqID: string;
  status: string;
  companyName: string;
  companyRegno: string;
  dateofissue: any;
  isGuarantyCompany: any;
  blockBackToForm = false;
  blockBackToForm2 = false;
  blockPayment = false;
  enableGoToPay = false;
  storage1: any;
  storage2: any;
  application = [];
  aditionalDocumentList = [];
  newSharesRecords = [];
  shinduvidual = [];
  sfirm = [];
  public country: Array<ICountry> = [];
  enableStep1Submission: boolean;
  validSharesEdit = false;
  hideAndshow = false;
  validationMessageSubmit = '';
  validationMessageType = false;
  url: APIConnection = new APIConnection();
  date = new Date();
  email = '';
  stepOn = 0;
  validationMessage = '';
  cipher_message: string;
  csvSupport: ICsvSupport = { id: null, title: null, countryname: null, province: null, district: null, city: null };
  shares: ISharesData = { id: 0, showEditPaneForMemb: false, typeofshare: null, dateofissue: null, issuedshares: null, noofsharesascash: null, consideration: null, noofsharesasnoncash: null, considerationotherthancash: null, considerationotherthancashtext: null, cashapplicability: null, noncashapplicability: null };

  @ViewChild('csvShareholdersUploadElem')
  csvShareholdersUploadElem: ElementRef;

  externalGlobalComment = '';

  totalAccepted = 0;
  totalIgnored = 0;
  totalAdded = 0;
  totalExist = 0;
  showItem = 0;
  showItemFirm = 0;
  indCount = 0;
  firmCount = 0;
  errorUloadMessage = '';
  errorUploadFlag = false;
  loginUserEmail: string;
  exampleCSVs = {};

  progress = {

    stepArr: [
      { label: 'Shares Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Shareholder Details', icon: 'fa fa-list-ol', status: '' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },

    ],

    progressPercentage: '10%'

  };


  // tslint:disable-next-line:no-shadowed-variable
  constructor(private router: Router, public calculation: CalculationService, private helper: HelperService, private spinner: NgxSpinnerService, private httpClient: HttpClient, public data: DataService, private general: GeneralService, private IssueOfSharesService: IssueOfSharesService, private AppLoad: AppLoadService) {
    if (JSON.parse(localStorage.getItem('Sharesstorage'))) {
      this.storage1 = JSON.parse(localStorage.getItem('Sharesstorage'));
      this.companyId = this.storage1['companyId'];
      this.changeReqID = this.storage1['changeReqID'];
      this.status = this.storage1['status'];

      this.spinner.show();

      this.AppLoad.getCountry()
      .subscribe(
        req => {
          this.country = req['countries'];
          this.loadShareTypes();
          this.loadCSVs(); // Load dummy data csv and sample data csv files
          this.loadUploadedFile();
          this.loadCompanyName();
          this.loadUploadShareholdersRecord(); // Load upload shareholders records to when processing
        }
      );

      this.loginUserEmail = localStorage.getItem('currentUser');
      this.changeProgressStatuses(0);
    }
  }

  ngOnInit() {
    // func
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

  // Load company name on beganing
  loadCompanyName() {
    const data = {
      id: this.companyId,
      changeReqID: this.changeReqID,
      type: 'resubmit'
    };
    this.IssueOfSharesService.loadCompanyName(data)
      .subscribe(
        req => {
          this.spinner.hide();
          if (req['status']) {
            this.companyName = req['data']['company'][0]['name'];
            this.companyRegno = req['data']['company'][0]['registration_no'];

            if (req['data']['processingshares']) {
              let x = 1;
              for (let i in req['data']['processingshares']) {
                const data1 = {
                  id: req['data']['processingshares'][i]['id'],
                  typeofshare: req['data']['processingshares'][i]['typeofshare'],
                  dateofissue: req['data']['processingshares'][i]['dateofissue'],
                  issuedshares: req['data']['processingshares'][i]['issuedshares'],
                  cashapplicability: req['data']['processingshares'][i]['cashapplicability'].toString(),
                  noofsharesascash: req['data']['processingshares'][i]['noofsharesascash'],
                  consideration: req['data']['processingshares'][i]['consideration'],
                  noncashapplicability: req['data']['processingshares'][i]['noncashapplicability'].toString(),
                  noofsharesasnoncash: req['data']['processingshares'][i]['noofsharesasnoncash'],
                  considerationotherthancash: req['data']['processingshares'][i]['considerationotherthancash'],
                  considerationotherthancashtext: req['data']['processingshares'][i]['considerationotherthancashtext'],
                };
                this.newSharesRecords.push(data1);

              }
            }
            this.externalGlobalComment = req['data']['external_global_comment'];
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  // Load share Types
  loadShareTypes() {
    this.IssueOfSharesService.initializeApp();
  }

  getEmail() {

    this.email = localStorage.getItem('currentUser');
    this.email = this.email.replace(/^"(.*)"$/, '$1');
    return this.email;
  }

  /*.....below show () functions for the radio buttons....*/
  show1edit(i) {
    document.getElementById('div1').style.display = 'block';
    document.getElementById('div2').style.display = 'block';
  }
  show2edit(i) {
    document.getElementById('div1').style.display = 'none';
    document.getElementById('div2').style.display = 'none';

    this.newSharesRecords[i]['noofsharesascash'] = null;
    this.newSharesRecords[i]['consideration'] = null;
  }
  show3edit(i) {
    document.getElementById('div3').style.display = 'block';
    document.getElementById('div4').style.display = 'block';
    document.getElementById('div5').style.display = 'block';
  }
  show4edit(i) {
    document.getElementById('div3').style.display = 'none';
    document.getElementById('div4').style.display = 'none';
    document.getElementById('div5').style.display = 'none';

    this.newSharesRecords[i]['noofsharesasnoncash'] = null;
    this.newSharesRecords[i]['considerationotherthancash'] = null;
    this.newSharesRecords[i]['considerationotherthancashtext'] = null;
  }
  show2after() {
    document.getElementById('div1').style.display = 'none';
    document.getElementById('div2').style.display = 'none';
  }
  show4after() {
    document.getElementById('div3').style.display = 'none';
    document.getElementById('div4').style.display = 'none';
    document.getElementById('div5').style.display = 'none';
  }
  /*.....above show () functions for the radio buttons....*/

  sharesDetailsValidationStep2(i) {

    if (
      this.newSharesRecords[i].typeofshare &&
      this.newSharesRecords[i].dateofissue &&
      this.newSharesRecords[i].issuedshares && parseFloat(this.newSharesRecords[i].issuedshares.toString()) &&
      this.newSharesRecords[i].cashapplicability !== null &&
      this.newSharesRecords[i].noncashapplicability !== null &&
      ( ( this.newSharesRecords[i].cashapplicability.toString() === '1' && this.newSharesRecords[i].noofsharesascash && parseFloat(this.newSharesRecords[i].noofsharesascash.toString()) && this.newSharesRecords[i].consideration && parseFloat(this.newSharesRecords[i].consideration.toString()) ) || this.newSharesRecords[i].cashapplicability.toString() === '0' ) &&
      ( ( this.newSharesRecords[i].noncashapplicability.toString() === '1' && this.newSharesRecords[i].noofsharesasnoncash && parseFloat(this.newSharesRecords[i].noofsharesasnoncash.toString()) && this.newSharesRecords[i].considerationotherthancash && parseFloat(this.newSharesRecords[i].considerationotherthancash.toString()) && this.newSharesRecords[i].considerationotherthancashtext ) || this.newSharesRecords[i].noncashapplicability.toString() === '0' )

      ) {
        this.validationMessage = '';
        this.validSharesEdit = true;
      } else {
        this.validationMessage = 'Please fill all  required fields correct denoted by asterik(*)';
        this.validSharesEdit = false;
      }

    /*if (

      this.newSharesRecords[i].typeofshare &&
      this.newSharesRecords[i].dateofissue &&
      this.newSharesRecords[i].issuedshares && Number(this.newSharesRecords[i].issuedshares)

    ) {

      if (
        ((this.newSharesRecords[i].cashapplicability == 1 &&
          this.newSharesRecords[i].noofsharesascash && Number(this.newSharesRecords[i].noofsharesascash) &&
          this.newSharesRecords[i].consideration && Number(this.newSharesRecords[i].consideration)) || (this.newSharesRecords[i].cashapplicability == 0 && this.newSharesRecords[i].noncashapplicability == 1))
        &&
        ((this.newSharesRecords[i].noncashapplicability == 1 &&
          this.newSharesRecords[i].noofsharesasnoncash && Number(this.newSharesRecords[i].noofsharesasnoncash) &&
          this.newSharesRecords[i].considerationotherthancash && Number(this.newSharesRecords[i].considerationotherthancash) && this.newSharesRecords[i].considerationotherthancashtext) || (this.newSharesRecords[i].noncashapplicability == 0 && this.newSharesRecords[i].cashapplicability == 1))
      ) {
        this.validationMessage = '';
        this.validSharesEdit = true;
      } else {
        this.validationMessage = 'Please fill all  required fields correct denoted by asterik(*)';
        this.validSharesEdit = false;
      }
    }
    else {
      this.validationMessage = 'Please fill all  required fields correct denoted by asterik(*)';
      this.validSharesEdit = false;
    }*/
  }

  // Edit data in debenture list...
  editSharesDataArray(i) {

    const data = {
      showEditPaneForMemb: false,
      id: this.newSharesRecords[i].id,
      typeofshare: this.newSharesRecords[i].typeofshare,
      dateofissue: this.newSharesRecords[i].dateofissue,
      issuedshares: this.newSharesRecords[i].issuedshares,
      cashapplicability: this.newSharesRecords[i].cashapplicability,
      noofsharesascash: this.newSharesRecords[i].noofsharesascash,
      consideration: this.newSharesRecords[i].consideration,
      noncashapplicability: this.newSharesRecords[i].noncashapplicability,
      noofsharesasnoncash: this.newSharesRecords[i].noofsharesasnoncash,
      considerationotherthancash: this.newSharesRecords[i].considerationotherthancash,
      considerationotherthancashtext: this.newSharesRecords[i].considerationotherthancashtext
    };
    this.newSharesRecords.splice(i, 1, data);
    this.validationMessageSubmit = '';
    this.hideAndshow = false;
  }

  // Hide and show list
  showToggle(index = 0) {
    this.newSharesRecords[index]['showEditPaneForMemb'] = !this.newSharesRecords[index]['showEditPaneForMemb'];
    this.hideAndshow = !this.hideAndshow;
    return true;
  }

  // Validate full list when submit...
  validateSharesList() {
    this.enableStep1Submission = true;
    if ((typeof this.newSharesRecords === 'undefined' || this.newSharesRecords == null || this.newSharesRecords.length == null || this.newSharesRecords.length === 0)) {
      this.validationMessageType = true;
      this.enableStep1Submission = false;
    } else {
      for (let i in this.newSharesRecords) {

        if (
          this.newSharesRecords[i].typeofshare &&
          this.newSharesRecords[i].dateofissue &&
          this.newSharesRecords[i].issuedshares && parseFloat(this.newSharesRecords[i].issuedshares.toString()) &&
          this.newSharesRecords[i].cashapplicability !== null &&
          this.newSharesRecords[i].noncashapplicability !== null &&
          ( ( this.newSharesRecords[i].cashapplicability.toString() === '1' && this.newSharesRecords[i].noofsharesascash && parseFloat(this.newSharesRecords[i].noofsharesascash.toString()) && this.newSharesRecords[i].consideration && parseFloat(this.newSharesRecords[i].consideration.toString()) ) || this.newSharesRecords[i].cashapplicability.toString() === '0' ) &&
          ( ( this.newSharesRecords[i].noncashapplicability.toString() === '1' && this.newSharesRecords[i].noofsharesasnoncash && parseFloat(this.newSharesRecords[i].noofsharesasnoncash.toString()) && this.newSharesRecords[i].considerationotherthancash && parseFloat(this.newSharesRecords[i].considerationotherthancash.toString()) && this.newSharesRecords[i].considerationotherthancashtext ) || this.newSharesRecords[i].noncashapplicability.toString() === '0' )

          ) {
            this.enableStep1Submission = true;
            this.validationMessageType = false;
          } else {
            this.enableStep1Submission = false;
            this.validationMessageType = false;
          }

       /* if (

          this.newSharesRecords[i].typeofshare &&
          this.newSharesRecords[i].dateofissue &&
          this.newSharesRecords[i].issuedshares && Number(this.newSharesRecords[i].issuedshares)

        ) {

          if (
            ((this.newSharesRecords[i].cashapplicability == 1 &&
              this.newSharesRecords[i].noofsharesascash && Number(this.newSharesRecords[i].noofsharesascash) &&
              this.newSharesRecords[i].consideration && Number(this.newSharesRecords[i].consideration)) || (this.newSharesRecords[i].cashapplicability == 0 && this.newSharesRecords[i].noncashapplicability == 1))
            &&
            ((this.newSharesRecords[i].noncashapplicability == 1 &&
              this.newSharesRecords[i].noofsharesasnoncash && Number(this.newSharesRecords[i].noofsharesasnoncash) &&
              this.newSharesRecords[i].considerationotherthancash && Number(this.newSharesRecords[i].considerationotherthancash) && this.newSharesRecords[i].considerationotherthancashtext) || (this.newSharesRecords[i].noncashapplicability == 0 && this.newSharesRecords[i].cashapplicability == 1))
          ) {
          } else {
            this.enableStep1Submission = false;
            this.validationMessageType = false;
          }
        }
        else {
          this.enableStep1Submission = false;
          this.validationMessageType = false;
        }*/


      }
    }
    this.stepOneSubmission();
  }

  // Check status before submit...
  stepOneSubmission() {
    if (this.enableStep1Submission) {
      this.shareDetailsReSubmit();
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

  // Shares details resubmit...
  shareDetailsReSubmit() {
    const data = {

      comId: this.companyId,
      changeReqID: this.changeReqID,
      newSharesRecords: this.newSharesRecords,
      email: this.getEmail(),
    };

    this.IssueOfSharesService.sharesReSubmit(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.changeProgressStatuses(1);
            this.blockBackToForm = false;
          }
        },
        error => {
          console.log(error);
          this.blockBackToForm = false;
        }
      );

  }

  // Load dummy data csv and sample data csv files
  loadCSVs() {
    this.IssueOfSharesService.getCSVs()
      .subscribe(
        response => {
          if (response['status']) {
            this.exampleCSVs = response['data'];
          }
        },
        error => {
          console.log(error);

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
      // additionally add for for known user and changeREqID
      formData.append('loginUserEmail', this.loginUserEmail);
      formData.append('changeReqID', this.changeReqID);


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
              this.loadUploadShareholdersRecord();

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

  changeProgresstoStep3() {
    this.changeProgressStatuses(2);
    this.blockBackToForm2 = false;
  }

  // Load upload excell shareholders records count...
  loadUploadShareholdersRecord() {
    const data = {
      comId: this.companyId,
      changeReqID: this.changeReqID,
    };

    this.IssueOfSharesService.loadExcellData(data)
      .subscribe(
        response => {
          if (response['status']) {
            this.totalExist = response['data'];
            this.shinduvidual = response['induvidual'];
            this.indCount = response['indCount'];
            this.sfirm = response['firm'];
            this.firmCount = response['firmCount'];
            this.spinner.hide();
          }
        },
        error => {
          console.log(error);
          this.spinner.hide();
        }
      );
  }

  // Reset shareholde records...
  resetShareholderRecords() {
    const data = {
      comId: this.companyId,
      changeReqID: this.changeReqID,
    };
    this.IssueOfSharesService.getResetShareholderRecordsService(data)
      .subscribe(
        response => {
          if (response['status']) {
            this.loadUploadShareholdersRecord();
          }
        },
        error => {
          console.log(error);
        }
      );


  }

  prevItem(i) {
    this.showItem = i - 1;
  }
  nextItem(i) {
    this.showItem = i + 1;
  }

  prevItemFirm(i) {
    this.showItemFirm = i - 1;
  }
  nextItemFirm(i) {
    this.showItemFirm = i + 1;
  }

  // issue of share Form 6 download
  issueofSharesFormGeneratePDF() {

    const data = {
      changeReqID: this.changeReqID,
      comId: this.companyId,
      email: this.getEmail(),

    };

    this.IssueOfSharesService.getIssueofShareFormPDFService(data)
      .subscribe(
        response => {
          this.helper.download(response);
        },
        error => {
          console.log(error);

        }
      );
  }

  // Current shareholders details pdf download
  currentShareholdersDetailsGeneratePDF() {
    const data = {
      changeReqID: this.changeReqID,
      comId: this.companyId,
      email: this.getEmail(),

    };

    this.IssueOfSharesService.getCurrentShareholdersDetailsPDFService(data)
      .subscribe(
        response => {
          this.helper.download(response);
        },
        error => {
          console.log(error);

        }
      );
  }


  // for uplaod updated issue of share pdf files...
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
      formData.append('comId', this.storage1['comId']);
      formData.append('changeReqID', this.changeReqID);
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getIssueofShareUpdatedFileUploadUrl();
      this.spinner.show();



      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {


            this.loadUploadedFile();
            this.spinner.hide();


          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }

  }


  loadUploadedFile() {

    const data = {
      comId: this.companyId,
      changeReqID: this.changeReqID,
      type: 'resubmit'
    };
    this.IssueOfSharesService.issueofsharesFiles(data)
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
                  company_document_id: req['data']['file'][i]['company_document_id'],
                  comments: req['data']['file'][i]['comments'],
                  value: req['data']['file'][i]['value'],
                  setKey: req['data']['file'][i]['setKey']
                };
                if (req['data']['file'][i]['docKey'] === 'ISSUE_OF_SHARES_FORM6') {
                  this.application.push(data1);

                } else if (req['data']['file'][i]['docKey'] === 'ISSUE_OF_SHARES_ADDITIONAL_DOCUMENT') {
                  this.aditionalDocumentList.push(data1);
                }
              }
              this.gotoPay(this.application, this.aditionalDocumentList);
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
  fileDeleteUpdate(docId, docType, index) {
    this.spinner.show();
    const data = {
      documentId: docId,
      type: docType,
    };

    this.IssueOfSharesService.issueofsharesDeleteUploadedUpdatePdf(data)
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

  gotoPay(application, aditionalDocumentList) {

    if (application) {

      for (let i in application) {

        if (application[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;

          return false;
        }
        else {
          continue;
        }
      }
      for (let i in aditionalDocumentList) {

        if (aditionalDocumentList[i].setKey === 'DOCUMENT_REQUESTED') {
          this.enableGoToPay = false;

          return false;
        }
        else {
          continue;
        }
      }

    }


    this.enableGoToPay = true;
    return true;


  }

  // For complete issue of shares resubmit process
  issueofsharesReSubmit() {

    const data = {
      changeReqID: this.changeReqID,
    };

    this.IssueOfSharesService.issueofsharesReSubmit(data)
      .subscribe(
        req => {

          localStorage.removeItem('Sharesstorage');
          this.router.navigate(['/dashboard/home']);

        },
        error => {
          console.log(error);
        }
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

  areYouSureYes2() {
    this.blockBackToForm2 = true;
  }
  areYouSureNo2() {
    this.blockBackToForm2 = false;
  }

}

