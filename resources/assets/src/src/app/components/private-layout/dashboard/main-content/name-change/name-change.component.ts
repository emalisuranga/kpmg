import { Component, OnInit } from '@angular/core';
import { NameChangeService } from 'src/app/http/services/name-change.service';
import { DataService } from 'src/app/storage/data.service';
import { mFormData, NameDetails } from './data/formData.model';
import { FormDataService } from './data/formData.service';
import { Router } from '@angular/router';
import { GeneralService } from 'src/app/http/services/general.service';

@Component({
  selector: 'app-name-change',
  templateUrl: './name-change.component.html',
  styleUrls: ['./name-change.component.scss']
})
export class NameChangeComponent implements OnInit {
  formData: Array<mFormData>;
  nameDetails: NameDetails = new NameDetails;
  public newName: string;
  public oldName: string;
  public MaxDate: number;
  public MaxAmount: number;
  constructor(private data: DataService, private formdataservice: FormDataService, private details: NameChangeService, private route: Router, private general: GeneralService) { }

  ngOnInit() {
    var id = this.data.getFormData[0];
    var newid = this.data.getFormData[1];
    this.newName = this.data.getFormData[2];
    this.oldName = this.data.getFormData[3];
    var resubmit = this.data.getFormData[4];
    var reDate = this.data.getFormData[5];

    this.general.getSetting('PENALTY_LIMITED_DATE', 'key').subscribe(req => {
      console.log(req);
    });
    this.general.getSetting('PENALTY_LIMITED_MAX_AMOUNT', 'key').subscribe(req => {
     console.log(req);
    });
    console.log(this.MaxDate);
    console.log(this.MaxAmount);

    if (newid === undefined) {
      this.route.navigate(['/dashboard/home']);
    }
    this.formdataservice.setOldRefNumber(id, newid, resubmit, reDate);
    this.data.cleanData();
  }

}
