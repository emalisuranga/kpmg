import { ActivatedComponent } from './activated/activated.component';
import { UserActivationRoutingModule } from './user-activation-routing.module';
import { ActivationComponent } from './activation/activation.component';
import { NgModule, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MigrateUserComponent } from './migrate-user/migrate-user.component';

@NgModule({
  imports: [
    CommonModule,
    UserActivationRoutingModule,
  ],
  declarations: [
    ActivatedComponent,
    ActivationComponent,
    MigrateUserComponent
  ],
  schemas: [
    CUSTOM_ELEMENTS_SCHEMA
  ],
})
export class UserActivationModule { }
