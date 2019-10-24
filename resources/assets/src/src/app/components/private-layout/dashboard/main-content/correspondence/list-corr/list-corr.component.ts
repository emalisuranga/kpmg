import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { CorrService } from '../services/corr.service';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import {IgetCompanies, IgetCompany, IgetCorrespondence, IgetCorrespondenceList } from '../models/corrModel';
import { GeneralService } from '../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { DataService } from '../../../../../../storage/data.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../../storage/ibuy-details';


@Component({
  selector: 'app-list-corr',
  templateUrl: './list-corr.component.html',
  styleUrls: ['./list-corr.component.scss']
})
export class ListCorrComponent implements OnInit {

  url: APIConnection = new APIConnection();

  namePart = '';
  registration_no = '';
  request_id = '';

  emptyCloseTenderMessage = '';

  totalResultPages: number = null;
  currentPage: 1;
  companyList: IgetCorrespondenceList = { list: [] };
  company: IgetCorrespondence = {request_id: null, company_id: null, company_name: '', status: '', date: '', comment: '' , reg_no: ''};

  constructor(
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private iNcoreService: CorrService,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private general: GeneralService,
    private helper: HelperService,
    public pdc: DataService,
    private crToken: PaymentService,
    private iBy: IBuyDetails) {

  }


  ngOnInit() {
    this.getCorrespondenceList();
  }

  getCorrespondenceList(page = 1 ) {

    page = ( isNaN(page) ) ? 0 : ( page - 1 ) ;
    page = (page <= 0 ) ? 0 : page;


    const data = {
      namePart : this.namePart,
      registration_no: this.registration_no,
      request_id: this.request_id,
      page : page
    };
    // this.spinner.show();

  //  if (!(this.namePart || this.registration_no || this.request_id) ){
  //    this.spinner.hide();
  //    return false;
  //  }

    // load Company data from the server
    this.iNcoreService.getUserCorrespondenceList(data)
      .subscribe(
        req => {
            let corrs = req['CorrespondenceList'];
            this.companyList.list = [];
            for ( let i in corrs){

              let c: IgetCorrespondence = {
                request_id : corrs[i]['request_id'],
                company_id : corrs[i]['company_id'],
                company_name: corrs[i]['company_name'],
                status: corrs[i]['status'],
                date: corrs[i]['date'],
                comment: corrs[i]['comment'],
                reg_no : corrs[i]['reg_no']
             };

             this.companyList.list.push(c);

            }

            // tslint:disable-next-line:radix
            this.totalResultPages = parseInt( req['total_pages'] );
            this.currentPage = req['current_page'];

            // this.spinner.hide();


        }
      );
  }


  goToNew(companyId) {
      this.router.navigate(['dashboard/correspondence/' + companyId ]);
  }
  goToExist( companyId, requestId) {
    this.router.navigate(['dashboard/correspondence/' + companyId + '/' + requestId ]);
  }

  createNew() {
    this.router.navigate(['dashboard/correspondence-search-companies/']);
  }

}


