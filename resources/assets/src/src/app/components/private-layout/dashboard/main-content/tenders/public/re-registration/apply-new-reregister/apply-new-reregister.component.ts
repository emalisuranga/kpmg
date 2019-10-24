import { Component, OnInit } from '@angular/core';
import { TenderService } from '../../../services/tender.service';
import { ICloseTenderItem, ICloseTenderItems, ITender, ItenderListItems, ItenderListItem, IapplyTender, IapplyTenderDirectors, IapplyTenderMembers, IapplyTenderShareHolders, IapplyTenderDirector, IapplyTenderShareHolder, IapplyTenderMember, IDownloadDocs, IUploadDocs} from '../../../models/tender.model';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { APITenderConnection } from '../../../services/connections/APITenderConnection';
import { ActivatedRoute, Router } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { DomSanitizer } from '@angular/platform-browser';
import { GeneralService } from '../../../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../../../http/shared/helper.service';
import { DataService } from '../../../../../../../../storage/data.service';
import { PaymentService } from '../../../../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../../../../http/models/payment';
import { Icountry } from '../../../../../../../../http/models/incorporation.model';
import { environment } from '../../../../../../../../../environments/environment';

@Component({
  selector: 'app-apply-new-reregister',
  templateUrl: './apply-new-reregister.component.html',
  styleUrls: ['./apply-new-reregister.component.scss']
})
export class ApplyNewReregisterComponent implements OnInit {

  url: APITenderConnection = new APITenderConnection();

  tender_id: string;
  item_id: number;
  application_id: number;
  type: string;


  constructor( private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private tenderService: TenderService,
    private  sanitizer: DomSanitizer,
    private general: GeneralService,
    private helper: HelperService,
    public pdc: DataService,
    private crToken: PaymentService,
    private iBy: IBuyDetails
    ) {
      this.tender_id = route.snapshot.paramMap.get('tender_id');
      // tslint:disable-next-line:radix
      this.item_id = parseInt( route.snapshot.paramMap.get('item_id') );
      // tslint:disable-next-line:radix
      this.application_id =  parseInt(route.snapshot.paramMap.get('application_id'));
      this.type = route.snapshot.paramMap.get('type');

      this.createNewRenewalReRegRequest();

  }

  ngOnInit() {

  }


  createNewRenewalReRegRequest() {

    const data = {
      item_id : this.item_id,
      application_id: this.application_id,
      type: this.type,
      renewal_or_rereg : 'reregistration'
    };
    this.spinner.show();

    // load Company data from the server
    this.tenderService.tenderRenewalReregNewRecord(data)
      .subscribe(
        req => {

          if ( req['status'] === false ) {
            this.router.navigate(['/dashboard/tenders-applied']);
            return false;
          }

          let token = req['token'];
          this.router.navigate(['/home/tenders/re-register/apply/' + this.tender_id + '/' + token ]);

          this.spinner.hide();

        }
      );

  }


}

