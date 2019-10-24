import { GeneralService } from '../../../../http/services/general.service';
import { AuthenticationService } from '../../../../http/services/authentication.service';
import { IStatusCount } from '../../../../http/models/recervationdata.model';
import { Component, OnInit, AfterViewInit } from '@angular/core';

@Component({
  selector: 'app-horizontal-status-bar',
  templateUrl: './horizontal-status-bar.component.html',
  styleUrls: ['./horizontal-status-bar.component.scss']
})
export class HorizontalStatusBarComponent {

  public status: IStatusCount =  {} as any;
  constructor(
    public AuthService: AuthenticationService,
    public generalService: GeneralService
  ) {
    this.getStatus();
  }

  getStatus(): void {
    this.generalService.getStatusCount().subscribe(
      req => {
        this.status = req;
      }
    );
  }

}
