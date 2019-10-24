
import { Component, OnInit } from '@angular/core';
import { TenderService } from '../../services/tender.service';
import { ICloseTenderItem, ICloseTenderItems, ITender, ItenderListItems, ItenderListItem, IapplyTender, IapplyTenderDirectors, IapplyTenderMembers, IapplyTenderShareHolders, IapplyTenderDirector, IapplyTenderShareHolder, IapplyTenderMember, IDownloadDocs, IUploadDocs} from '../../models/tender.model';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { APITenderConnection } from '../../services/connections/APITenderConnection';
import { ActivatedRoute, Router } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { DomSanitizer } from '@angular/platform-browser';
import { GeneralService } from '../../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../../http/shared/helper.service';
import { IBuy, Item } from '../../../../../../../http/models/payment';
import { Icountry } from '../../../../../../../http/models/incorporation.model';
import { environment } from '../../../../../../../../environments/environment';

@Component({
  selector: 'app-applied-tenders',
  templateUrl: './applied-tenders.component.html',
  styleUrls: ['./applied-tenders.component.scss']
})
export class AppliedTendersComponent implements OnInit {


  applicationList = [];


  constructor( private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private tenderService: TenderService,
    private  sanitizer: DomSanitizer,
    private general: GeneralService,
    private helper: HelperService
    ) {

     // get application LIST
     this.getUserApplications();

  }

  ngOnInit() {


  }

  sanitize(url: string) {
    return this.sanitizer.bypassSecurityTrustUrl(url);
  }

  ngOnDownload(token: string): void {

    this.spinner.show();
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

  getUserApplications() {

    this.spinner.show();

    // load Company data from the server
    this.tenderService.getUserApplications(null)
      .subscribe(
        req => {
          this.applicationList = req['applicationList'];
          this.spinner.hide();
        }
      );



  }

  updateApplication(tenderId) {
    this.router.navigate(['/home/tenders/apply/', tenderId]);
  }

  resubmitApplication(tenderId, resubmitToken) {
    this.router.navigate(['/home/tenders/resubmit/', { tenderId: tenderId, token: resubmitToken }]);
  }

  awordItem(tenderId, awordToken) {
    console.log(tenderId);
    console.log(awordToken);
    this.router.navigate(['/home/tenders/awarding/', { tenderId: tenderId, token: awordToken }]);
  }


}


