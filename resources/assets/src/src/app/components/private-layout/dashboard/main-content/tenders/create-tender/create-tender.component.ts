import { Component, OnInit, AfterViewInit, ElementRef } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';

import * as $ from 'jquery';
import { TenderService } from '../services/tender.service';
import { ItenderPublications, ICloseTenderMember, ICloseTenderMembers, ICloseTenderItem, ICloseTenderItems, ITender} from '../models/tender.model';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { APITenderConnection } from '../services/connections/APITenderConnection';
import { ViewChild } from '@angular/core';
import * as Papa from 'papaparse';
@Component({
  selector: 'app-tender',
  templateUrl: './create-tender.component.html',
  styleUrls: ['./create-tender.component.scss'],
})
export class CreateTenderComponent implements OnInit, AfterViewInit {

  url: APITenderConnection = new APITenderConnection();

  @ViewChild('csvAplicantUploadElem')
  csvUploadElem: ElementRef;

  stepOn = 0;
  closeApplictionCSV = '';

  publicationsList: ItenderPublications = { publications : [] };
  tender: ITender = {
  // publisherType: null,
  tenderLimit: 'upper-limit',
  tenderType : null,
  tenderNo: null,
  tenderName: null,
  description: null,
  loginUser: localStorage.getItem('currentUser'),
  publicationId: 0,
  newPublicationName: '',
  tenderAmount: null,
  ministry: '',
  department: '',
  division: '',
  authorized_person_name: '',
  authorized_person_designation: '',
  authorized_person_address: '',
  authorized_person_phone: '',
  authorized_person_email: '',
  bid_data_sheet: '',
  paper_advertisement: ''
};

tenderMembers: ICloseTenderMembers = { members: [] };
tenderMember: ICloseTenderMember = { name: '', address: '', contactNo: '', email: '' };

tenderItems: ICloseTenderItems = { items: [] };
tenderItem: ICloseTenderItem = { name: '', description: '', qty: null, dateFrom: '', dateTo: '' };

tenderAmountCutOff = 5000000;
tenderAmountCutOffValueText = 'five milion';
cutOffFailMessage = 'Tender amount must be above ' + this.tenderAmountCutOffValueText;

tenderSubmissionMessage = '';


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

/****item total value  */
totalItemValue = 0;


progress = {

    stepArr: [
      { label: 'Initiate/Tender Details', icon: 'fas fa-play-circle', status: 'active' },
      { label: 'Tender Items/Publish', icon: 'fas fa-check-double', status: '' },
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
    private tenderService: TenderService
      ) {

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
      // this.tender.tenderAmount && this.tender.tenderAmount >= this.tenderAmountCutOff &&
       ( this.tender.publicationId || this.tender.newPublicationName ) &&
       this.tender.description
     //  this.validatDate( this.tender.dateFrom ) &&
     //  this.validatDate( this.tender.dateTo ) &&
     //  (this.tender.dateTo > this.tender.dateFrom )

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

    if ( !(
      this.tenderItem.name &&
      this.tenderItem.name &&
     // this.tenderItem.qty &&
      this.validatDate( this.tenderItem.dateFrom ) &&
      this.validatDate( this.tenderItem.dateTo ) &&
     (this.tenderItem.dateTo > this.tenderItem.dateFrom )

    ) ) {
       this.isValidItem = false;
    } else {
      this.isValidItem = true;
    }
 }

 validateStep2() {
   // if ( this.tenderItems.items.length && this.validateItemPriceWithCutOffMark() ) {
    if ( this.tenderItems.items.length ) {
      this.isValidStep2 = true;
    } else {
      this.isValidStep2 = false;
    }
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

  fileChange($e) {

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

  addTenderItem() {

    // tslint:disable-next-line:prefer-const
    let copy = Object.assign({}, this.tenderItem);
    this.tenderItems.items.push(copy);
    this.tenderItem = { name: '', description: '', qty: null , dateFrom: '' , dateTo: '' };
    this.validateAddItem();
    this.validateStep2();

   // console.log(this.tenderItems.items);

  }

  validateItemPriceWithCutOffMark() {

      /*let tenderItems, totalItemPrice;
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

      console.log(totalItemPrice);

      if ( totalItemPrice >= this.cutOffPrice ) {
        this.cutOffFailMessage = '';
        return true;
      } else {
        this.cutOffFailMessage = 'Total cost of all items must be greater than 5 milion';
        return false;
      }*/

  }

  removeItem(i, itemId = null) {
    this.tenderItems.items.splice(i, 1);
    this.validateItemPriceWithCutOffMark();
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
     // dateFrom : this.tender.dateFrom,
     // dateTo : this.tender.dateTo,
      tenderLimit : this.tender.tenderLimit,
   //   publisherType:  this.tender.publisherType,
      tenderMembers: this.tenderMembers,
      loginUser : localStorage.getItem('currentUser'),
      tenderId : ( this.tender.tenderId ) ? this.tender.tenderId : 0,
      publicationId : this.tender.publicationId,
      newPublicationName: this.tender.newPublicationName,
      tenderAmount: this.tender.tenderAmount,
      ministry : this.tender.ministry,
      department : this.tender.department,
      division : this.tender.division,
      authorized_person_name : this.tender.authorized_person_name,
      authorized_person_designation : this.tender.authorized_person_designation,
      authorized_person_address : this.tender.authorized_person_address,
      authorized_person_phone : this.tender.authorized_person_phone,
      authorized_person_email : this.tender.authorized_person_email
    };
    this.spinner.show();

    // load Company data from the server
    this.tenderService.tenderAdd(data)
      .subscribe(
        req => {

          if ( req['status'] === true ) {

            this.spinner.hide();
            this.tender.tenderId = req.tenderId;
            this.tender.publicationId = req.publicationId;
            this.tender.newPublicationName = '';

            this.router.navigate(['dashboard/tenders/edit-tender/' + this.tender.tenderId]);
            this.getPublicationsList();
            this.changeProgressStatuses(1);
          } else {
            this.changeProgressStatuses(0);
          }
        }
      );



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
          this.tenderMembers = { members: [] };
          this.tenderItems = { items: [] };

  }

  submitTenderItem() {

    const data = {
      items: this.tenderItems,
      tenderId : ( this.tender['tenderId'] ) ? this.tender['tenderId'] : 0
    };
    this.spinner.show();
    this.tenderService.tenderAddItems(data)
      .subscribe(
        req => {

          if ( req['status'] === true ) {
           this.spinner.hide();
           this.changeProgressStatuses(0);
           // this.router.navigate(['dashboard/tenders/create-tender']);
           this.tenderSubmissionMessage = 'Successfully created a Tender.';
           this.resetForNewTender();
          } else {
            this.changeProgressStatuses(1);
            this.tenderSubmissionMessage = '';
          }
        }
      );

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
    if (!d) {
      return false;
    }
    // tslint:disable-next-line:prefer-const
    let regx = /^\d{4}([./-])\d{2}\1\d{2}$/;
    return  d.match(regx);
  }

  private validateItemCost(c) {

    if (!c) {
      return false;
    }
    return parseFloat(c) > 0 ;

  }


}
