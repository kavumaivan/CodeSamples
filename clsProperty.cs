/*
 *  Created by Ivan kavuma April 26 2006
 * 
 *  RentMatters Inc
 * 
 *  Property Class for Appartment Evaluator Application.  
 * 
 * 
 */



using System;
using System.Collections.Generic;
using System.Data;
using System.Text;
using System.Data.SqlClient;
using System.Windows.Forms;
namespace Apt_Evaluator
{
    class clsProperty
    {
             public static string ConnectionString = clsConnection.ConnectionString;


        /// <summary>
        /// 
        /// </summary>
        /// <returns>DataSet Properties</returns>
        public DataSet GetAllMain()
        {
            try
            {
                using (SqlConnection conn = new SqlConnection(ConnectionString))
                {
                    string SqlQuery = "Exec dbo.sp_CRUD_Property @CRUD='R', @Property_Type = 'Main'"; 

                    SqlDataAdapter adapter = new SqlDataAdapter(SqlQuery, conn);
                    conn.Open();
                    DataSet ds = new DataSet();
                    adapter.Fill(ds, "Property");
                    return ds;

                }
            }
            catch (Exception ex)
            {
                throw (ex);
            }
        }


        
        
       /// <summary>
        ///  ReadProperty
       /// </summary>
       /// <param name="PropertyID"></param>
       /// <returns></returns>
 
        public DataSet ReadProperty(int PropertyID)
        {
            try
            {
                using (SqlConnection conn = new SqlConnection(ConnectionString))
                {
                    string SqlQuery = "exec dbo.sp_CRUD_Property @CRUD='R' ,@PropertyID =" + PropertyID;

                    SqlDataAdapter adapter = new SqlDataAdapter(SqlQuery, conn);
                    conn.Open();
                    DataSet ds = new DataSet();
                    adapter.Fill(ds, "Property");
                    return ds;
                    
                }
            }
            catch (Exception ex)
            {
                throw (ex);
            }
        }

        /// <summary>
        ///     CreateProperty.
        /// </summary>
        /// <param name="PropertyName"></param>
        /// <param name="Address"></param>
        /// <param name="City"></param>
        /// <param name="State"></param>
        /// <param name="Zip"></param>
        /// <param name="Country"></param>
        /// <param name="Number_of_Units"></param>
        /// <param name="Square_Ft"></param>
        /// <param name="Purchase_Price"></param>
        /// <param name="Prepared_For"></param>
        /// <returns></returns>
        public int CreateProperty(string PropertyName,
                                  string Address ,
                                  string City ,
                                  string State ,
                                  string Zip,
                                  string Country,
                                  int Number_of_Units,
                                  Single Square_Ft,
                                  decimal Purchase_Price,
                                  string Prepared_For,
                                  DateTime Date_of_Purchase,
                                  Decimal Current_Property_Value,
                                  string Property_Type
                                  )
        {
            try{
            using (SqlConnection conn = new SqlConnection(ConnectionString))
            {
                string SqlQuery = "exec dbo.sp_CRUD_Property @CRUD='C'" +
                                        " ,@PropertyName ='" + PropertyName +
                                        "' ,@Address ='" + Address +
                                        "' ,@City ='" + City +
                                        "' ,@State ='" + State +
                                        "' ,@Zip ='" + Zip +
                                        "' ,@Country ='" + Country +
                                        "' ,@Number_of_Units ='" + Number_of_Units +
                                        "' ,@Square_Ft ='" + Square_Ft +
                                        "' ,@Purchase_Price ='" + Purchase_Price +
                                        "' ,@Prepared_For ='" + Prepared_For +
                                        "' ,@Date_of_Purchase ='" + Date_of_Purchase +
                                        "' ,@Current_Property_Value ='" + Current_Property_Value +
                                        "' ,@Property_Type ='" + Property_Type +
                                        "'" ;

                    SqlCommand cmd = new SqlCommand( SqlQuery, conn);
                    conn.Open();
                    SqlDataReader reader = cmd.ExecuteReader();
                    while (reader.Read())
                    {   
                        return Convert.ToInt16(reader[0]);

                    }

                }
           

                return 0;
            }
            catch (Exception ex)
            {
                throw (ex);
            }
        }


        /// <summary>
        ///   UpdateProperty
        /// </summary>
        /// <param name="PropertyID"></param>
        /// <param name="PropertyName"></param>
        /// <param name="Address"></param>
        /// <param name="City"></param>
        /// <param name="State"></param>
        /// <param name="Zip"></param>
        /// <param name="Country"></param>
        /// <param name="Number_of_Units"></param>
        /// <param name="Square_Ft"></param>
        /// <param name="Purchase_Price"></param>
        /// <param name="Prepared_For"></param>
        /// <returns></returns>
        public int UpdateProperty(int PropertyID,
                                  string PropertyName,
                                  string Address,
                                  string City,
                                  string State,
                                  string Zip,
                                  string Country,
                                  int Number_of_Units,
                                  Single Square_Ft,
                                  decimal Purchase_Price,
                                  string Prepared_For,
                                  DateTime Date_of_Purchase,
                                  Decimal Current_Property_Value
                               )
        {
            try
            {
                using (SqlConnection conn = new SqlConnection(ConnectionString))
                {
                    string SqlQuery = "exec dbo.sp_CRUD_Property @CRUD='U'" +
                                            " ,@PropertyID =" + PropertyID +
                                            " ,@PropertyName ='" + PropertyName +
                                            "' ,@Address ='" + Address +
                                            "' ,@City ='" + City +
                                            "' ,@State ='" + State +
                                            "' ,@Zip ='" + Zip +
                                            "' ,@Country ='" + Country +
                                            "' ,@Number_of_Units ='" + Number_of_Units +
                                            "' ,@Square_Ft ='" + Square_Ft +
                                            "' ,@Purchase_Price ='" + Purchase_Price +
                                            "' ,@Prepared_For ='" + Prepared_For +
                                            "' ,@Date_of_Purchase ='" + Date_of_Purchase +
                                            "' ,@Current_Property_Value ='" + Current_Property_Value +
                                            "'";

                    SqlCommand cmd = new SqlCommand(SqlQuery, conn);
                    conn.Open();
                    cmd.ExecuteNonQuery();
                    

                    return PropertyID;
                }
            }
            catch (Exception ex)
            {
                throw (ex);
            }

      }


/// <summary>
/// 
/// </summary>
/// <param name="PropertyID"></param>
/// <param name="Year_Built"></param>
/// <param name="Managed_By"></param>
/// <param name="Survey_Date"></param>
/// <param name="Property_Type"></param>
/// <returns></returns>
        public int UpdateProperty(int PropertyID,
    	                            int Year_Built,
                                    string Managed_By,
                                    DateTime Survey_Date
                                    
                                 )
        {
            try
            {
                using (SqlConnection conn = new SqlConnection(ConnectionString))
                {
                    string SqlQuery = "exec dbo.sp_CRUD_Property @CRUD='U'" +
                                            " ,@PropertyID =" + PropertyID +
                                            " ,@Year_Built ='" + Year_Built +
                                            "' ,@Managed_By ='" + Managed_By +
                                            "' ,@Survey_Date ='" + Survey_Date +
                                            "'";

                    SqlCommand cmd = new SqlCommand(SqlQuery, conn);
                    conn.Open();
                    cmd.ExecuteNonQuery();
                    

                    return PropertyID;
                }
            }
            catch (Exception ex)
            {
                throw (ex);
            }

      }
        /// <summary>
        /// 
        /// </summary>
        /// <param name="PropertyID"></param>
        /// <returns></returns>

        public int DeleteProperty(int PropertyID )
        {
            try
            {
                using (SqlConnection conn = new SqlConnection(ConnectionString))
                {
                    string SqlQuery = "exec dbo.sp_CRUD_Property @CRUD='D'" +
                                            " ,@PropertyID =" + PropertyID  ;

                    SqlCommand cmd = new SqlCommand(SqlQuery, conn);
                    conn.Open();
                    SqlDataReader reader = cmd.ExecuteReader();


                    return PropertyID;
                }
            }
            catch (Exception ex)
            {
                throw (ex);
            }
        }

        //#######################################################################
        //  ADD corporate information.
        //#######################################################################


        public DataSet ReadCorporate()
        {
            try
            {
                using (SqlConnection conn = new SqlConnection(ConnectionString))
                {
                    string SqlQuery = "Exec dbo.sp_CRUD_Property @CRUD='R', @Property_Type = 'Corp'";

                    SqlDataAdapter adapter = new SqlDataAdapter(SqlQuery, conn);
                    conn.Open();
                    DataSet ds = new DataSet();
                    adapter.Fill(ds, "Property");
                    return ds;

                }
            }
            catch (Exception ex)
            {
                throw (ex);
            }
        }





        /// <summary>
        /// 
        /// </summary>
        /// <param name="PropertyName"></param>
        /// <param name="Address"></param>
        /// <param name="City"></param>
        /// <param name="State"></param>
        /// <param name="Zip"></param>
        /// <param name="Country"></param>
        /// <returns></returns>

        public int CreateCorporate(string PropertyName,
                                 string Address,
                                 string City,
                                 string State,
                                 string Zip,
                                 string Country
                                 )
        {
            try
            {
                using (SqlConnection conn = new SqlConnection(ConnectionString))
                {
                    string SqlQuery = "Exec dbo.sp_CRUD_Property @CRUD='C'" +
                                            " ,@PropertyName ='" + PropertyName +
                                            "' ,@Address ='" + Address +
                                            "' ,@City ='" + City +
                                            "' ,@State ='" + State +
                                            "' ,@Zip ='" + Zip +
                                            "' ,@Country ='" + Country +
                                            "' ,@Property_Type = 'Corp'" ;

                    SqlCommand cmd = new SqlCommand(SqlQuery, conn);
                    conn.Open();
                    SqlDataReader reader = cmd.ExecuteReader();
                    while (reader.Read())
                    {
                        return Convert.ToInt16(reader[0]);

                    }

                }


                return 0;
            }
            catch (Exception ex)
            {
                throw (ex);
            }
        }


        public int UpdateCorporate(int PropertyID,
                                 string PropertyName,
                                 string Address,
                                 string City,
                                 string State,
                                 string Zip,
                                 string Country
                              )
        {
            try
            {
                using (SqlConnection conn = new SqlConnection(ConnectionString))
                {
                    string SqlQuery = "exec dbo.sp_CRUD_Property @CRUD='U'" +
                                            "  ,@PropertyID =" + PropertyID +
                                            "  ,@PropertyName ='" + PropertyName +
                                            "' ,@Address ='" + Address +
                                            "' ,@City ='" + City +
                                            "' ,@State ='" + State +
                                            "' ,@Zip ='" + Zip +
                                            "' ,@Country ='" + Country +
                                            "'";

                    SqlCommand cmd = new SqlCommand(SqlQuery, conn);
                    conn.Open();
                    cmd.ExecuteNonQuery();


                    return PropertyID;
                }
            }
            catch (Exception ex)
            {
                throw (ex);
            }

        }

        /*##############################################################################
             Rent and Sales Comparable
        /################################################################################*/

        /// <summary>
        ///     CreateProperty.
        /// </summary>
        /// <param name="PropertyName"></param>
        /// <param name="Address"></param>
        /// <param name="City"></param>
        /// <param name="State"></param>
        /// <param name="Zip"></param>
        /// <param name="Country"></param>
        /// <param name="Number_of_Units"></param>
        /// <param name="Square_Ft"></param>
        /// <param name="Purchase_Price"></param>
        /// <param name="Prepared_For"></param>
        /// <returns></returns>
        public int CreateProperty(string PropertyName,
                                  string Address,
                                  string City,
                                  string State,
                                  string Zip,
                                  string Country,
                                  string Property_Type
                                  )
        {
            try
            {
                using (SqlConnection conn = new SqlConnection(ConnectionString))
                {
                    string SqlQuery = "exec dbo.sp_CRUD_Property @CRUD='C'" +
                                            " ,@PropertyName ='" + PropertyName +
                                            "' ,@Address ='" + Address +
                                            "' ,@City ='" + City +
                                            "' ,@State ='" + State +
                                            "' ,@Zip ='" + Zip +
                                            "' ,@Country ='" + Country +
                                            "' ,@Property_Type ='" + Property_Type +
                                            "'";

                    SqlCommand cmd = new SqlCommand(SqlQuery, conn);
                    conn.Open();
                    SqlDataReader reader = cmd.ExecuteReader();
                    while (reader.Read())
                    {
                        return Convert.ToInt16(reader[0]);

                    }

                }


                return 0;
            }
            catch (Exception ex)
            {
                throw (ex);
            }
        }


        /// <summary>
        ///   UpdateProperty
        /// </summary>
        /// <param name="PropertyID"></param>
        /// <param name="PropertyName"></param>
        /// <param name="Address"></param>
        /// <param name="City"></param>
        /// <param name="State"></param>
        /// <param name="Zip"></param>
        /// <param name="Country"></param>
        /// <param name="Number_of_Units"></param>
        /// <param name="Square_Ft"></param>
        /// <param name="Purchase_Price"></param>
        /// <param name="Prepared_For"></param>
        /// <returns></returns>
        public int UpdateProperty(int PropertyID,
                                  string PropertyName,
                                  string Address,
                                  string City,
                                  string State,
                                  string Zip,
                                  string Country
                               )
        {
            try
            {
                using (SqlConnection conn = new SqlConnection(ConnectionString))
                {
                    string SqlQuery = "exec dbo.sp_CRUD_Property @CRUD='U'" +
                                            " ,@PropertyID =" + PropertyID +
                                            " ,@PropertyName ='" + PropertyName +
                                            "' ,@Address ='" + Address +
                                            "' ,@City ='" + City +
                                            "' ,@State ='" + State +
                                            "' ,@Zip ='" + Zip +
                                            "' ,@Country ='" + Country +
                                            "'";

                    SqlCommand cmd = new SqlCommand(SqlQuery, conn);
                    conn.Open();
                    cmd.ExecuteNonQuery();


                    return PropertyID;
                }
            }
            catch (Exception ex)
            {
                throw (ex);
            }

        }


     }//end class
}
