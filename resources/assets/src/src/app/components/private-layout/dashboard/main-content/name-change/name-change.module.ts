import { NgModule, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SelectNameChangeComponent } from './select-name-change/select-name-change.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NameChangeRoutingModule } from './name-change-routing.module';
import { NameChangeComponent } from './name-change.component';
import { WorkflowService } from './workflow/workflow.service';
import { NgSelectModule } from '@ng-select/ng-select';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    NameChangeRoutingModule,
    NgSelectModule
  ],
  declarations: [
    SelectNameChangeComponent,
    NameChangeComponent
  ],
  schemas: [
    CUSTOM_ELEMENTS_SCHEMA
  ],
  providers: [
    { provide: WorkflowService, useClass: WorkflowService }
  ],
})
export class NameChangeModule { }
