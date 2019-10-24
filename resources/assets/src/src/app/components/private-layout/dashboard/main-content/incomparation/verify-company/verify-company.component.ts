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
import { IcompanyInfo, IcompanyAddress, IcompanyType, IcompnayTypesItem, IcompanyObjective, IloginUserAddress, IloginUser, IcoreShareGroup, Icountry, IcompanyForAddress, IirdInfo } from '../../../../../../http/models/incorporation.model';
import { count, distinct } from 'rxjs/operators';
import { GeneralService } from '../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { DataService } from '../../../../../../storage/data.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../../http/models/payment';
import { isNull } from '@angular/compiler/src/output/output_ast';
@Component({
  selector: 'app-verify-company',
  templateUrl: './verify-company.component.html',
  styleUrls: ['./verify-company.component.scss']
})
export class VerifyCompanyComponent implements OnInit, AfterViewInit {

  url: APIConnection = new APIConnection();


  // company id
  certificateNo: string;
  loginUserEmail: string;
  verified = false;
  verificationMessage = '';
  companyName: '';
  incorpAt: '';

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
    private iBy: IBuyDetails
    ) {

    this.certificateNo = route.snapshot.paramMap.get('certificateNo');
    this.loginUserEmail = localStorage.getItem('currentUser');
    this.checkVerification();
  }

  ngAfterViewInit() {

    $(document).on('click', '.record-handler-remove', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      self.parent().parent().remove();
    });

    $('button.add-director').on('click', function () {
      $('#director-modal .close-modal-item').trigger('click');
    });

    $('button.add-sec').on('click', function () {
      $('#sec-modal .close-modal-item').trigger('click');
    });

    $('button.add-share').on('click', function () {
      $('#share-modal .close-modal-item').trigger('click');
    });

    $('.stakeholder-type-tab-wrapper.stakeholder-dir .tab').on('click', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      $('.stakeholder-type-tab-wrapper.stakeholder-dir .tab').removeClass('active');
      $(this).addClass('active');

    });

    $('.stakeholder-type-tab-wrapper.stakeholder-sec .tab').on('click', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      $('.stakeholder-type-tab-wrapper.stakeholder-sec .tab').removeClass('active');
      $(this).addClass('active');

    });

    $('.stakeholder-type-tab-wrapper.stakeholder-sh .tab').on('click', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      $('.stakeholder-type-tab-wrapper.stakeholder-sh .tab').removeClass('active');
      $(this).addClass('active');

    });


  }

  ngOnInit() {

    this.spinner.show();

  }

  sanitize(url: string) {
    return this.sanitizer.bypassSecurityTrustUrl(url);
  }

  checkVerification(){
    const data = {
      certificateNo: this.certificateNo,
    };
    this.spinner.show();
    this.iNcoreService.verifyCompany(data)
      .subscribe(
        rq => {
           if (rq['status']) {
              this.verificationMessage = rq['message'];
              this.verified = true;
              this.companyName = rq['company_name'];
              this.incorpAt = rq['incorporation_at'];
           } else {
            this.verificationMessage = rq['message'];
            this.verified = false;
            this.companyName = '';
            this.incorpAt = '';
           }
           this.spinner.hide();

        },
        error => {
          this.verificationMessage = '';
          this.verified = false;
          this.companyName = '';
          this.incorpAt = '';
          alert('Something went wrong. Please try again later.');
          this.spinner.hide();
          console.log(error);
        }

      );
  }



}


