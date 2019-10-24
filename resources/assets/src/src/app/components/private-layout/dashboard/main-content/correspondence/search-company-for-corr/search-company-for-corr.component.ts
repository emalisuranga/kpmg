import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { CorrService } from '../services/corr.service';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import {IgetCompanies, IgetCompany } from '../models/corrModel';
import { GeneralService } from '../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { DataService } from '../../../../../../storage/data.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../../storage/ibuy-details';


@Component({
  selector: 'app-search-company-for-corr',
  templateUrl: './search-company-for-corr.component.html',
  styleUrls: ['./search-company-for-corr.component.scss']
})
export class SearchCompanyForCorrComponent implements OnInit {

  url: APIConnection = new APIConnection();

  namePart = '';
  registration_no = '';

  emptyCloseTenderMessage = '';

  totalResultPages: number = null;
  currentPage: 1;
  companyList: IgetCompanies = { list: [] };
  company: IgetCompany = {id: null, name: null, regNo: '', incorporation_at: '', name_si: '', name_ta: '', postfix: '' };
  correspondents = [];


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
    this.getCompanies();
  }

  getCompanies(page = 1 ) {

    page = ( isNaN(page) ) ? 0 : ( page - 1 ) ;
    page = (page <= 0 ) ? 0 : page;


    const data = {
      namePart : this.namePart,
      registration_no: this.registration_no,
      page : page
    };
    this.spinner.show();

    if (!(this.namePart || this.registration_no) ){
      this.spinner.hide();
      return false;
    }

    // load Company data from the server
    this.iNcoreService.getCompaniesForCertificates(data)
      .subscribe(
        req => {
            let companies = req['companyList'];
            this.companyList.list = [];
            for ( let i in companies){

              let c: IgetCompany = {
                id : companies[i]['id'],
                name: companies[i]['name'],
                name_si: companies[i]['name_si'],
                name_ta: companies[i]['name_ta'],
                regNo: companies[i]['registration_no'],
                incorporation_at: companies[i]['incorporation_at'],
                postfix: companies[i]['postfix'],
                init_name_of_the_company : companies[i]['init_name_of_the_company'],
                init_name_of_the_company_id : companies[i]['init_name_of_the_company_id'],
                init_name_of_the_company_incorporation_at : companies[i]['init_name_of_the_company_incorporation_at'],
                is_name_change_company_instant : companies[i]['is_name_change_company_instant'],
                init_name_of_the_company_postfix : companies[i]['init_name_of_the_company_postfix'],
                correspondents : companies[i]['correspondence']

             };

             this.companyList.list.push(c);

            }

            // tslint:disable-next-line:radix
            this.totalResultPages = parseInt( req['total_pages'] );
            this.currentPage = req['current_page'];

            this.spinner.hide();


        }
      );
  }


  goToNew(companyId) {
      this.router.navigate(['dashboard/correspondence/' + companyId ]);
  }
  goToExist( companyId, requestId) {
    this.router.navigate(['dashboard/correspondence/' + companyId + '/' + requestId ]);
  }

}

