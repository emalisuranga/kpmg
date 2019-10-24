import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { IncorporationService } from '../../../../../../http/services/incorporation.service';
import { IDirectors, IDirector, ISecretories, ISecretory, IShareHolders, IShareHolder, IShareHolderBenif, IShareHolderBenifList, IProvince, IDistrict, ICity, IObjective, IGnDivision, IObjectiveRow, IObjectiveCollection } from '../../../../../../http/models/stakeholder.model';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { IcompanyInfo, IcompanyAddress, IcompanyType, IcompnayTypesItem, IcompanyObjective, IloginUserAddress, IloginUser, IcoreShareGroup, Icountry, IcompanyForAddress, IirdInfo, IgetCompanies, IgetCompany } from '../../../../../../http/models/incorporation.model';
import { count, distinct } from 'rxjs/operators';
import { GeneralService } from '../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { DataService } from '../../../../../../storage/data.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../../http/models/payment';
import { isNull } from '@angular/compiler/src/output/output_ast';

@Component({
  selector: 'app-get-certified-copies',
  templateUrl: './get-certificates.component.html',
  styleUrls: ['./get-certificates.component.scss']
})
export class GetCertificatesComponent implements OnInit {

  url: APIConnection = new APIConnection();

  namePart = '';
  registration_no = '';

  emptyCloseTenderMessage = '';

  totalResultPages: number = null;
  currentPage: 1;
  companyList: IgetCompanies = { list: [] };
  company: IgetCompany = {id: null, name: null, regNo: '', incorporation_at: '', name_si: '', name_ta: '', postfix: '' };


  constructor(
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private iNcoreService: IncorporationService,
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

    if (!(this.namePart || this.registration_no )){
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
                init_name_of_the_company_postfix : companies[i]['init_name_of_the_company_postfix']

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


  goToCompany(companyId) {

      this.router.navigate(['dashboard/get-certificates/' + companyId ]);
    //  this.router.navigate(['/home/tenders/resubmit/' + tenderId + '/' + '111' ]);
    // this.router.navigate(['/home/tenders/awarding/' + tenderId + '/' + 'a0c02989502252329f1a9e38f04510b1' ]);
  }

}
