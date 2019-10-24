import { Component, OnInit, AfterViewInit, ElementRef } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';

import * as $ from 'jquery';
import * as Papa from 'papaparse';
import { TenderService } from '../services/tender.service';
import { ICloseTenderMember, ICloseTenderMembers, ICloseTenderItem, ICloseTenderItems, ITender, ItenderPublications, IUploadDocs} from '../models/tender.model';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { APITenderConnection } from '../services/connections/APITenderConnection';
import { GeneralService } from '../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { ViewChild } from '@angular/core';
@Component({
  selector: 'app-tender',
  templateUrl: './edit-tender.component.html',
  styleUrls: ['./edit-tender.component.scss'],
})
export class EditTenderComponent implements OnInit, AfterViewInit {

  url: APITenderConnection = new APITenderConnection();

  recordTenderId: number;
  tenderAmountCutOff = 5000000;
  tenderAmountCutOffValueText = 'five milion';
  cutOffFailMessage = 'Tender amount must be above ' + this.tenderAmountCutOffValueText;

  @ViewChild('csvAplicantUploadElem')
  csvUploadElem: ElementRef;

  stepOn = 0;
  showAwordAction = false;
  publicationsList: ItenderPublications = { publications : [] };
  appliedList = {};
  awordedList = {};
  winnerList = {};
  tender: ITender = {
   // publisherType: null,
   tenderLimit: null,
   tenderType : null,
   tenderStatusCode: '',
   tenderNo: null,
   tenderName: null,
   description: null,
   loginUser: localStorage.getItem('currentUser'),
   publicationId: 0,
   newPublicationName: '',
   tenderAmount: null,
   appliedCount : 0,
   ministry: '',
   department: '',
   division: '',
   authorized_person_name: '',
   authorized_person_designation: '',
   authorized_person_address: '',
   authorized_person_phone: '',
   authorized_person_email: '',
   bid_data_sheet: '',
   paper_advertisement: '',
   'paper_advertisement_file_name' : '',
   bid_data_ext: '',
   paper_ad_ext: ''
};

tenderMembers: ICloseTenderMembers = { members: [] };
tenderMember: ICloseTenderMember = { name: '', address: '', contactNo: '', email: '' };

tenderItems: ICloseTenderItems = { items: [] };
tenderItem: ICloseTenderItem = { name: '', description: '', qty: null, dateFrom: '', dateTo: '', itemId: null, itemNo: null };

awordedApplicant = '';
/**validation flags */

// flag for validate members on adding for close tenders
isValidCreateCloseMember = false;
// flag for validate tender - step1
isValidStep1 = false;

// flage for validate add item
isValidItem = false;

// flag for validate tender - step2
isValidStep2 = false;

/****Messages */
step1DateToFromMessage = '';
editMessage = '';
openEditModel = false;

publishPress = false;
saveAs = 'draft';

/****item total value  */
totalItemValue = 0;
awardMessage = '';

openItemDateToUpdated = false;

closeApplictionCSV = '';


uploadOtherList: IUploadDocs = { docs: [] };
other_doc_name = '';


progress = {

    stepArr: [
      { label: 'Edit Tender Details', icon: 'fas fa-play-circle', status: 'active' },
      { label: 'Edit items and Finish', icon: 'fas fa-edit', status: '' },
     // { label: 'Download Documents', icon: 'fa fa-download', status: '' },
    //  { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },

    ],
    progressPercentage: '25%'

  };

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private tenderService: TenderService,
    private general: GeneralService,
    private helper: HelperService,
      ) {

        // tslint:disable-next-line:radix
    this.recordTenderId = parseInt( route.snapshot.paramMap.get('tenderId') );

    // get tender details with publications
    this.getPublicationsList();
  }


  ngAfterViewInit() {


    $(document).on('click', '.stakeholder-record-summeru', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      self.next($('.stakeholder-record-inner').eq(0)).stop().slideToggle(300);
    });

    $(document).on('click', '.record-handler-remove', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      self.parent().parent().remove();
    });

    $('button.add-member').on('click', function () {
      $('#tender-close-member .close-modal-item').trigger('click');
    });

    $('button.add-sec').on('click', function () {
      $('#sec-modal .close-modal-item').trigger('click');
    });

    $('button.add-share').on('click', function () {
      $('#share-modal .close-modal-item').trigger('click');
    });

    $('.stakeholder-type-tab-wrapper .tab').on('click', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      $('.stakeholder-type-tab-wrapper .tab').removeClass('active');
      $(this).addClass('active');

    });


  }

  ngOnInit() {

   // this.spinner.show();

  }

  ngOnDownload(token: string): void {

    this.spinner.show();
   // this.general.getDocumenttoServer(token, 'CAT_TENDER_DOCUMENT')
    this.general.getDocumenttoServer(token, 'CAT_TENDER_PUBLISHER_DOCUMENT')
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

  ngOnDownloadCertificate(token: string): void {

    this.spinner.show();
   // this.general.getDocumenttoServer(token, 'CAT_TENDER_DOCUMENT')
    this.general.getDocumenttoServer(token, 'CAT_TENDER_CERTIFICATE_DOCUMENT')
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

  changeProgressStatuses(newStatus = 0) {
    this.stepOn = newStatus;

    this.progress.progressPercentage = (this.stepOn >= 2) ? (25 * 2 + this.stepOn * 50) + '%' : (25 + this.stepOn * 50) + '%';

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
  resetForNewTender() {

    this.tender.tenderLimit = 'upper-limit';
          this.tender.tenderType = null;
          this.tender.tenderNo = null;
          this.tender.tenderName = null;
          this.tender.description = null;
          this.tender.loginUser = localStorage.getItem('currentUser'),
          this.tender.newPublicationName = '',
          this.tender.tenderAmount = null;
          this.tender.tenderId = null;
          this.tender.ministry = '';
          this.tender.department = '';
          this.tender.division = '';
          this.tender.authorized_person_name = '';
          this.tender.authorized_person_designation = '';
          this.tender.authorized_person_address = '';
          this.tender.authorized_person_phone = '';
          this.tender.authorized_person_email = '';
          this.tender.bid_data_sheet = '';
          this.tender.paper_advertisement = '';
          this.tender.paper_advertisement_file_name = '';
          this.tender.bid_data_ext = '';
          this.tender.paper_ad_ext = '';

          this.tenderMembers = { members: [] };
          this.tenderItems = { items: [] };

          this.changeProgressStatuses(0);

  }

  getPublicationsList() {

    const data = {
      loginUser : localStorage.getItem('currentUser'),
    };
    this.spinner.show();

    // load Company data from the server
    this.tenderService.publicationsGet(data)
      .subscribe(
        req => {
          if ( req['status'] === true ) {
            this.spinner.hide();
            this.publicationsList.publications = req['publications'];
            this.closeApplictionCSV = req['close_tender_applicant_csv'];
          } else {
            this.publicationsList.publications = [];
          }
          this.getTender();
        }
      );



  }

  convertoFloat(val) {
    return parseFloat(val);
  }

  validateAwardRecord(itemId) {

    let total = 0;

    if ( undefined !== this.appliedList[itemId] && this.appliedList[itemId].length) {
      for (let i in this.appliedList[itemId]) {
        total +=  ( this.appliedList[itemId][i]['awarded_portion'] && parseFloat( this.appliedList[itemId][i]['awarded_portion']) )  ? parseFloat(this.appliedList[itemId][i]['awarded_portion']) : 0;
      }
    }

    return (total > 0 && total <= 100 ) ;

  }

  getTender() {

    const data = {
      tenderId : this.recordTenderId
    };
    // this.spinner.show();

    // load Company data from the server
    this.tenderService.tenderGet(data)
      .subscribe(
        req => {

            // tslint:disable-next-line:prefer-const
            let tenderInfo = req['tenderInfo'];
            this.tender.tenderNo = tenderInfo['number'];
            this.tender.tenderName = tenderInfo['name'];
            this.tender.ministry = tenderInfo['ministry'];
            this.tender.department = tenderInfo['department'];
            this.tender.division = tenderInfo['division'];
            this.tender.authorized_person_name = tenderInfo['authorized_person_name'];
            this.tender.authorized_person_designation = tenderInfo['authorized_person_designation'];
            this.tender.authorized_person_address =  tenderInfo['authorized_person_address'];
            this.tender.authorized_person_phone = tenderInfo['authorized_person_phone'];
            this.tender.authorized_person_email = tenderInfo['authorized_person_email'];
            this.tender.bid_data_sheet = tenderInfo['bid_data_sheet'];
            this.tender.bid_data_ext = req['bid_data_type'];
            this.tender.paper_ad_ext = req['paper_ad_type'];
            this.tender.paper_advertisement = req['paper_advertisement'];
            this.tender.paper_advertisement_file_name = req['paper_advertisement_file_name'];
            this.tender.tenderType = req['tenderStatus'];
            this.tender.tenderStatusCode = req['tenderStatusCode'];
            this.tender.description = tenderInfo['description'];
            this.tender.tenderLimit = 'upper-limit';
            this.tender.tenderId = tenderInfo['id'];
            this.tender.tenderAmount = tenderInfo['amount'];
            this.tender.publicationId = tenderInfo['publication_id'];
            this.tender.appliedCount = req['appliedCount'];
            this.appliedList = req['applied_list'];

            // tslint:disable-next-line:prefer-const
            for ( let i in this.appliedList ) {
                this.awordedList[i] = null;
                this.winnerList[i] = null;
            }

            this.winnerList = req['winnerList'];

          //  console.log(this.awordedList);

            // tslint:disable-next-line:prefer-const
            let tenderUser = req['tenderUsers'];

            this.tenderMembers.members = [];

            // tslint:disable-next-line:prefer-const
            for ( let i in tenderUser ) {

                // tslint:disable-next-line:prefer-const
                let m: ICloseTenderMember = {
                   name: tenderUser[i]['name'],
                   address: tenderUser[i]['address'],
                   contactNo: tenderUser[i]['contact_no'],
                   email: tenderUser[i]['email'],
                   memberId: tenderUser[i]['id']

                };
                this.tenderMembers.members.push(m);

            }

            this.tenderItems.items = [];

            // tslint:disable-next-line:prefer-const
            let tItems = req['tenderItems'];

            // tslint:disable-next-line:prefer-const
            for ( let i in tItems ) {
             // tslint:disable-next-line:prefer-const
             let itm: ICloseTenderItem = {
              name:  tItems[i]['name'],
              description: tItems[i]['description'],
              // tslint:disable-next-line:radix
              qty: parseInt( tItems[i]['quantity'] ),
              // tslint:disable-next-line:radix
              dateFrom : tItems[i]['from_time'] && parseInt(tItems[i]['from_time']) ? new Date( parseInt(tItems[i]['from_time']) * 1000 ) : new Date(),
              // tslint:disable-next-line:radix
              dateTo : tItems[i]['to_time'] && parseInt(tItems[i]['to_time']) ? new Date( parseInt(tItems[i]['to_time']) * 1000 ) : new Date(),
              itemId: tItems[i]['id'],
              itemNo: tItems[i]['number']
             };
             this.tenderItems.items.push(itm);

            }

            // console.log( this.tenderItems.items);


            if (this.tender.appliedCount) {
              this.progress.stepArr = [
                { label: 'Tender Details', icon: 'fas fa-list-ul', status: 'active' },
                { label: 'View applicants/ Choose winner', icon: 'fas fa-user-check', status: '' }
                ];
            }

            this.uploadOtherList = req['publisherDocs'];

            this.validateStep1();
            this.validateStep2();
            this.spinner.hide();

        }
      );



  }

  removeTenderDoc(docTypeId) {

    const data = {
      tenderId: this.recordTenderId,
      docType: docTypeId
    };
    this.spinner.show();
    this.tenderService.removeTenderSepecificDoc(data)
      .subscribe(
        rq => {
          this.getTender();
          this.spinner.hide();
        },
        error => {
          this.getTender();
          alert(error);
        }

      );


  }


  fileChange(event, doc_type ) {

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
      formData.append('uploadTenderFile', file, file.name);
      formData.append('fileRealName', file.name );
      formData.append('docType', doc_type );
      formData.append('tenderId',  this.route.snapshot.paramMap.get('tenderId') );

      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.getTendeDocUploadURL();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            if ( data['error'] === 'no' ) {
              this.getTender();
            }

            if ( data['error'] === 'yes') {
              alert( data['message']);
            }
            this.spinner.hide();
          },
          error => {
            alert(error);
            this.spinner.hide();
          }
        );
    }

  }

  fileChangeUploadCSV(event) {

    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      // tslint:disable-next-line:prefer-const
      let file: File = fileList[0];

      Papa.parse(fileList[0], {
        header: true,
        skipEmptyLines: true,
        complete: (result) => {
        //  console.log(result.data);

          if (result.data.length) {
              for ( let i in result.data ) {
                  let m: ICloseTenderMember = {name: '' , address: '', contactNo: '', email: ''};
                 // console.log(result.data[i]);
                   m.name = result.data[i]['Applicant Name'];
                   m.address = result.data[i]['Address'];
                   m.contactNo = result.data[i]['Contact Number'];
                   m.email = result.data[i]['Email'];

                   if ( m.name && m.address && m.contactNo && m.email && this.validateEmail(m.email) ) {
                    this.tenderMembers.members.push(m);
                   }

              }
          } else {
            alert('Invalid or No record found on your CSV file.');
          }
          this.csvUploadElem.nativeElement.value = '';
        }
      });

    } else {
        alert('No file uploaded');
        this.csvUploadElem.nativeElement.value = '';
    }

  }


  uploadOtherDoc(event, fileNane, fileDBID ) {

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
      formData.append('fileDescription', this.other_doc_name);
      formData.append('fileRealName', file.name );
      formData.append('fileTypeId', fileDBID);
      formData.append('tenderId',  this.route.snapshot.paramMap.get('tenderId') );
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.getPublisherOtherFileUploadURL();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.other_doc_name = '';
            this.getTender();

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
    this.tenderService.removeTenderPublisherOtherDoc(data)
      .subscribe(
        req => {
          this.getTender();
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

  awordSelectedApplicants(itemId, applicationId, type= '' ) {

    const data = {
      tenderId : this.recordTenderId,
      awordedList: this.appliedList,
      itemId: itemId,
      applicationId: applicationId

    //  awordedList: this.awordedList
    };
     this.spinner.show();

    // console.log(this.awordedList);
    this.awardMessage = '';
    // load Company data from the server
    this.tenderService.tenderAwordByPublisher(data)
      .subscribe(
        req => {
          this.winnerList = req['winnerList'];
          this.awardMessage = (type === 'cancel') ? 'The selected applicant has been cancelled with the award of the tender'  : 'The selected applicant has been awarded with the tender';
          this.showAwordAction = false;
          this.spinner.hide();

        }
      );



  }

  closeAfterAwordMessage() {
    this.awardMessage = '';
    this.getTender();
  }

  tenderItemDateChange(item: ICloseTenderItem, extend = false) {

    if (extend) {
      let alert = confirm('Are you sure you want to extend the date ?');

      if (!alert) {
        this.getTender();
        return false;
      }
    }

    const data = {
      itemId: item.itemId.toString(),
      dateTo : item.dateTo,
    };
    this.spinner.show();
    this.tenderService.changeItemCloseDateByPublisher(data)
      .subscribe(
        req => {
          this.openItemDateToUpdated = true;
          this.spinner.hide();
        }
      );
  }




  awordList (itemId, applicantId) {

    this.awordedList[itemId] = applicantId;
    this.showAwordAction = true;
  }

  cancelawordList(itemId, applicantId) {

    this.awordedList[itemId] = null;
  }



  validateCloseMember() {

    if (
      !(this.tenderMember.name &&
      this.tenderMember.address &&
      this.tenderMember.contactNo && this.phonenumber( this.tenderMember.contactNo) &&
      this.tenderMember.email && this.validateEmail (this.tenderMember.email)
    ) ) {
      this.isValidCreateCloseMember = false;
      return false;
    } else {
      this.isValidCreateCloseMember = true;
      return true;
    }


  }
  validateStep1() {

    if ( !(
      ( this.tender.tenderType === 'close' ? this.tenderMembers.members.length : true ) &&
      this.tender.tenderNo &&
      this.tender.tenderName &&
    //  this.tender.tenderAmount && parseFloat( this.tender.tenderAmount.toString()) &&
    //  this.tender.tenderAmount && this.tender.tenderAmount >= this.tenderAmountCutOff &&
      ( this.tender.publicationId || this.tender.newPublicationName ) &&
      this.tender.description &&
    //  this.tender.ministry &&
    //  this.tender.department &&
      this.tender.division &&
      this.tender.authorized_person_name &&
      this.tender.authorized_person_designation &&
      this.tender.authorized_person_address &&
     // this.phonenumber( this.tender.authorized_person_phone ) &&
      this.validateEmail(this.tender.authorized_person_email)

    ) ) {
       this.dateFromToMessage();
       this.isValidStep1 = false;
    } else {
     this.dateFromToMessage();
     this.isValidStep1 = true;
    }
    if ( this.tender.tenderAmount >= this.tenderAmountCutOff ) {
      this.cutOffFailMessage = '';
    } else {
      this.cutOffFailMessage = 'Tender amount must be above ' + this.tenderAmountCutOffValueText;
    }
 }

 validateAddItem() {
   console.log('came');

 // console.log(this.tenderItem.dateFrom);
 // console.log(this.tenderItem.dateTo);

  if ( !(
    this.tenderItem.name &&
    this.tenderItem.description &&
    ( new Date(this.tenderItem.dateTo) > new Date(this.tenderItem.dateFrom ) )
   // this.tenderItem.qty &&
  //  this.validatDate( this.tenderItem.dateFrom ) &&
 //   this.validatDate( this.tenderItem.dateTo ) &&
 //  (this.tenderItem.dateTo > this.tenderItem.dateFrom )

  ) ) {
     this.isValidItem = false;
  } else {
    this.isValidItem = true;
  }
}

validateStep2() {
  // if ( this.tenderItems.items.length && this.validateItemPriceWithCutOffMark() ) {
   if ( this.tenderItems.items.length && this.validedatesOnAddedItems() ) {
     this.isValidStep2 = true;
   } else {
     this.isValidStep2 = false;
   }
}

validedatesOnAddedItems() {
  // tslint:disable-next-line:prefer-const
  for ( let i in this.tenderItems.items ) {

    if (!(

     this.tenderItems.items[i].name &&
     this.tenderItems.items[i].description &&
     ( new Date(this.tenderItems.items[i].dateTo) > new Date(this.tenderItems.items[i].dateFrom ) )

    ) ) {
        return false;
    }
  }
  return true;
}

 validateItemPriceWithCutOffMark() {

 /* let tenderItems, totalItemPrice;
  tenderItems =  this.tenderItems.items;
  totalItemPrice = 0;


  if ( !tenderItems) {
    this.cutOffFailMessage = '';
    return false;
  }
  // tslint:disable-next-line:prefer-const
  for ( let i in tenderItems ) {
    totalItemPrice = totalItemPrice + parseFloat( tenderItems[i].cost ) ;
  }


  if ( totalItemPrice >= this.cutOffPrice ) {
    this.cutOffFailMessage = '';
    return true;
  } else {
    this.cutOffFailMessage = 'Total cost of all items must be greater than 5 milion';
    return false;
  }*/

}

dateFromToMessage() {
  if (this.validatDate( this.tenderItem.dateFrom ) && this.validatDate( this.tenderItem.dateTo ) ) {
    if ( this.tenderItem.dateFrom >= this.tenderItem.dateTo ) {
       this.step1DateToFromMessage = 'Date to must be after date from';
    } else {
      this.step1DateToFromMessage = '' ;
    }
  } else {
    this.step1DateToFromMessage = '' ;
  }
}

  saveMember() {
    // tslint:disable-next-line:prefer-const
    let copy = Object.assign({}, this.tenderMember);
    this.tenderMembers.members.push(copy);
    this.tenderMember = { name: '', address: '', contactNo: '', email: '' };

    this.validateStep1();

  }

  removeMember(i, memberId= null) {

    this.tenderMembers.members.splice(i, 1);
    if ( !memberId) {
       this.validateStep1();
        return true;
    }

    // else server call needed

  }


  addTenderItem() {

   // console.log(this.tenderItems.items);

    // tslint:disable-next-line:prefer-const
    let copy = Object.assign({}, this.tenderItem);

    let x = this.tenderItem.dateFrom.toString();
    copy.dateFrom = new Date(x);
    let y = this.tenderItem.dateTo.toString();
    copy.dateTo = new Date(y);

    console.log(copy);
   // return;

    this.tenderItems.items.push(copy);
    console.log(this.tenderItems.items);
    this.tenderItem = { name: '', description: '', qty: null , dateFrom: new Date() , dateTo: new Date() };
    this.validateAddItem();
    this.validateStep2();

    console.log(this.tenderItem);

  //  console.log(this.tenderItems.items);

  }

  removeItem(i, itemId = null) {
    this.tenderItems.items.splice(i, 1);
    // this.validateItemPriceWithCutOffMark();
    this.validateStep2();
    if (!itemId) {

        return true;
    }

  }

  submitTender() {

    const data = {
      tenderType: this.tender.tenderType,
      tenderNo: this.tender.tenderNo,
      tenderName: this.tender.tenderName,
      description : this.tender.description,
      ministry : this.tender.ministry,
      department : this.tender.department,
      division : this.tender.division,
      authorized_person_name : this.tender.authorized_person_name,
      authorized_person_designation : this.tender.authorized_person_designation,
      authorized_person_address : this.tender.authorized_person_address,
      authorized_person_phone : this.tender.authorized_person_phone,
      authorized_person_email: this.tender.authorized_person_email,
     // dateFrom : this.tender.dateFrom,
     // dateTo : this.tender.dateTo,
      tenderLimit : this.tender.tenderLimit,
   //   publisherType:  this.tender.publisherType,
      tenderMembers: this.tenderMembers,
      loginUser : localStorage.getItem('currentUser'),
      tenderId : ( this.tender.tenderId ) ? this.tender.tenderId : 0,
      publicationId : this.tender.publicationId,
      newPublicationName: this.tender.newPublicationName,
      tenderAmount: this.tender.tenderAmount
    };
    this.spinner.show();

    // load Company data from the server
    this.tenderService.tenderAdd(data)
      .subscribe(
        req => {

          if ( req['status'] === true ) {

            this.spinner.hide();
            this.tender.tenderId = req.tenderId;
            this.changeProgressStatuses(1);
            this.editMessage = 'Tender details are successfully updated';
            this.getTender();
          } else {
            this.changeProgressStatuses(0);
            this.editMessage = '';
          }
        }
      );



  }
  openEditModelFunc() {

    this.editMessage = '';
    this.openEditModel = false;

    if (this.saveAs === 'publish') {
      this.router.navigate(['/dashboard/tenders/list/']);
    } else {
      this.getTender();
    }
  }

  submitTenderItem() {

    const data = {
      items: this.tenderItems,
      tenderId : ( this.tender['tenderId'] ) ? this.tender['tenderId'] : 0,
      action: 'draft'
    };
    this.spinner.show();
    this.tenderService.tenderAddItems(data)
      .subscribe(
        req => {

          if ( req['status'] === true ) {
           this.spinner.hide();
           this.changeProgressStatuses(1);
           this.editMessage = 'Tender Items are successfully saved';
           this.openEditModel = true;
           this.saveAs = 'draft';


          } else {
            this.changeProgressStatuses(1);
            this.editMessage = '';
            this.openEditModel = false;
            this.saveAs = 'draft';
          }
        }
      );

  }

  publish() {
    this.publishPress = true;
  }
  publishCancel() {
    this.publishPress = false;
  }
  publishProceed() {

    this.publishPress = false;

    const data = {
      items: this.tenderItems,
      tenderId : ( this.tender['tenderId'] ) ? this.tender['tenderId'] : 0,
      action: 'publish'
    };
    this.spinner.show();
    this.tenderService.tenderAddItems(data)
      .subscribe(
        req => {

          if ( req['status'] === true ) {
           this.spinner.hide();
           this.changeProgressStatuses(1);
           this.editMessage = 'Tender Items are successfully Published';
           this.openEditModel = true;
           this.saveAs = 'publish';

          } else {
            this.changeProgressStatuses(1);
            this.editMessage = '';
            this.openEditModel = false;
            this.saveAs = 'draft';
          }
        }
      );

  }

  openItemDateToUpdatedFunc() {
    this.openItemDateToUpdated = false;
  }



  /*********util functions  */
    private  validateEmail(email) {
      if (!email) {
        return true;
      }
      // tslint:disable-next-line:prefer-const
      let  re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(String(email).toLowerCase());
  }
  private phonenumber(inputtxt) {
    if (!inputtxt) {
      return false;
    }
    // tslint:disable-next-line:prefer-const
    let phoneno = /^\d{10}$/;
    return  inputtxt.match(phoneno);
  }
  private validateNIC(nic) {
    if (!nic) {
      return false;
    }
    // tslint:disable-next-line:prefer-const
    let regx = /^[0-9]{9}[x|X|v|V]$/;
    return  nic.match(regx);
  }

  private validatDate(d) {

    return true;

  /*  if (!d) {
      return false;
    }
    // tslint:disable-next-line:prefer-const
    let regx = /^\d{4}([./-])\d{2}\1\d{2}$/;
    return  d.match(regx); */
  }

  private validateItemCost(c) {

    if (!c) {
      return false;
    }
    return parseFloat(c) > 0 ;

  }


}
