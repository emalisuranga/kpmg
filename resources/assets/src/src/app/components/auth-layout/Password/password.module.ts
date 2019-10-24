import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { PasswordRoutingModule } from './password-routing.module';
import { ResetPasswordComponent } from './reset-password/reset-password.component';
import { ForgotPasswordComponent } from './forgot-password/forgot-password.component';
import { NgModule, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { CommonModule } from '@angular/common';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    PasswordRoutingModule,
  ],
  declarations: [
    ForgotPasswordComponent,
    ResetPasswordComponent
  ],
  schemas: [
    CUSTOM_ELEMENTS_SCHEMA
  ],
})
export class PasswordModule { }
